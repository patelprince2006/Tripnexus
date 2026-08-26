import pymysql
import datetime
import os

def format_val(val, col_name, data_type):
    if val is None:
        return "NULL"
    
    # Handle boolean conversion for PostgreSQL
    if isinstance(val, bool) or col_name in ('is_verified', 'is_read', 'used'):
        if isinstance(val, (int, bool)):
            return "true" if val else "false"
    
    if isinstance(val, (int, float)):
        return str(val)
    
    if isinstance(val, (datetime.datetime, datetime.date)):
        return f"'{val.isoformat()}'"
        
    if isinstance(val, bytes):
        val = val.decode('utf-8', errors='replace')
        
    val_str = str(val).replace("'", "''")
    return f"'{val_str}'"

def main():
    db_config = {
        'host': 'localhost',
        'user': 'root',
        'password': '',
        'database': 'tripnexus',
        'port': 3306,
        'charset': 'utf8mb4'
    }

    try:
        conn = pymysql.connect(**db_config)
        cur = conn.cursor(pymysql.cursors.DictCursor)
        print("Connected to MySQL successfully.")
    except Exception as e:
        print(f"Error connecting to MySQL: {e}")
        return

    # Table sequence honoring foreign keys
    tables = [
        ('users', 'id'),
        ('admins', 'id'),
        ('airports', None),
        ('airlines', 'airline_id'),
        ('bus_locations', 'location_id'),
        ('train_stations', None),
        ('hotels', 'hotel_id'),
        ('hotel_rooms', 'id'),
        ('tour_packages', 'id'),
        ('flights', 'flight_id'),
        ('buses', 'bus_id'),
        ('trains', 'train_id'),
        ('bookings', 'id'),
        ('payments', 'id'),
        ('reviews', 'id'),
        ('notifications', 'id'),
        ('wishlist', 'id'),
        ('contact_messages', 'id'),
        ('password_resets', 'id'),
        ('tour_schedules', 'id')
    ]

    seed_lines = []
    seed_lines.append("-- TripNexus Live MySQL -> Supabase Data Seed Script")
    seed_lines.append("-- Generated automatically from local MySQL database: tripnexus")
    seed_lines.append(f"-- Generated At: {datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")
    seed_lines.append("BEGIN;\n")

    total_rows_exported = 0

    for table_name, pk_col in tables:
        try:
            cur.execute(f"SELECT * FROM `{table_name}`")
            rows = cur.fetchall()
            count = len(rows)
            if count == 0:
                print(f"Skipping table '{table_name}' (0 rows)")
                continue

            print(f"Exporting {count} rows from '{table_name}'...")
            seed_lines.append(f"-- Data for table: public.{table_name} ({count} rows)")
            
            # Fetch column metadata
            cur.execute(f"DESCRIBE `{table_name}`")
            cols_info = {col['Field']: col['Type'] for col in cur.fetchall()}

            for row in rows:
                col_names = list(row.keys())
                formatted_vals = [
                    format_val(row[col], col, cols_info.get(col, ''))
                    for col in col_names
                ]

                cols_str = ", ".join([f'"{c}"' for c in col_names])
                vals_str = ", ".join(formatted_vals)

                # Use ON CONFLICT DO NOTHING for primary key safety
                if pk_col and pk_col in col_names:
                    conflict_target = f'("{pk_col}")'
                elif table_name == 'airports':
                    conflict_target = '("airport_code")'
                elif table_name == 'train_stations':
                    conflict_target = '("station_code")'
                else:
                    conflict_target = ""

                if conflict_target:
                    sql_stmt = f'INSERT INTO public."{table_name}" ({cols_str}) VALUES ({vals_str}) ON CONFLICT {conflict_target} DO NOTHING;'
                else:
                    sql_stmt = f'INSERT INTO public."{table_name}" ({cols_str}) VALUES ({vals_str});'

                seed_lines.append(sql_stmt)

            seed_lines.append("")
            total_rows_exported += count

        except Exception as e:
            print(f"Error processing table {table_name}: {e}")

    # Reset sequences for identity columns
    seed_lines.append("-- Synchronize PostgreSQL identity sequences with max imported primary key IDs")
    for table_name, pk_col in tables:
        if pk_col:
            seq_reset_sql = (
                f"SELECT setval(pg_get_serial_sequence('public.\"{table_name}\"', '{pk_col}'), "
                f"COALESCE((SELECT MAX(\"{pk_col}\") FROM public.\"{table_name}\"), 1));"
            )
            seed_lines.append(seq_reset_sql)

    seed_lines.append("\nCOMMIT;\n")

    output_dir = os.path.join(os.path.dirname(__file__), '..', 'supabase')
    os.makedirs(output_dir, exist_ok=True)
    
    seed_file_path = os.path.join(output_dir, 'seed_data.sql')
    with open(seed_file_path, 'w', encoding='utf-8') as f:
        f.write("\n".join(seed_lines))

    print(f"\nSuccessfully generated '{seed_file_path}' with {total_rows_exported} total rows exported.")
    cur.close()
    conn.close()

if __name__ == '__main__':
    main()
