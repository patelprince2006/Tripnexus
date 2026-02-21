# Implementation Plan - Complete Admin Panel

## Goal Description
Build a comprehensive Admin Panel to manage Users, Flights, Hotels, Tours, Bookings, Payments, Reviews, and Notifications.

## User Review Required

- **Database Schema**: Significant additions (Hotels, Tours, Bookings).
- **Admin Path**: All admin files will be placed in an `admin/` subdirectory to keep the root clean.

## Proposed Changes

### 1. Database Schema Updates
#### [NEW] [admin/setup_admin_db.php]
Script to create the following tables:

- **`admins`**: `id`, `username`, `email`, `password` (hashed), `role`, `created_at`

- **`hotels`**: `id`, `name`, `city`, `address`, `description`, `price_per_night`, `rating`, `main_image`, `created_at`

- **`hotel_rooms`**: `id`, `hotel_id`, `room_type` (Deluxe, Suite), `price`, `available_count`

- **`tour_packages`**: `id`, `name`, `location`, `duration` (days), `price`, `description`, `itinerary` (text/json), `main_image`

- **`bookings`**: `id`, `user_id`, `type` (flight/hotel/package), `reference_id` (flight_id/hotel_id...), `booking_date`, `status` (pending/confirmed/cancelled/completed), `total_amount`

- **`payments`**: `id`, `booking_id`, `user_id`, `amount`, `status` (success/failed), `payment_date`, `transaction_id`

- **`reviews`**: `id`, `user_id`, `type`, `reference_id`, `rating`, `comment`, `status` (pending/approved), `created_at`

- **`notifications`**: `id`, `title`, `message`, `type` (offer/announcement), `sent_at`


#### [MODIFY] [Direct SQL Execution or Migration]
- Alter `users` table: Add `status` (active/blocked/deleted) default `active`.

### 2. Admin Authentication
#### [NEW] [admin/login.php]
- Admin login form.
#### [NEW] [admin/auth_check.php]
- Helper to include at the top of protected admin pages. Checks `$_SESSION['admin_logged_in']`.
#### [NEW] [admin/logout.php]
- Destroys admin session.

### 3. Admin Dashboard (Layout & Stats)
#### [NEW] [admin/includes/header.php] & [sidebar.php] & [footer.php]
- Common layout structure. Sidebar with links to all modules.
#### [NEW] [admin/dashboard.php]
- **Stats Cards**: Total Users, Total Bookings, Revenue, Active Flights/Hotels.
- **Charts**: Use Chart.js for "Monthly Bookings" and "Revenue".

### 4. User Management
#### [NEW] [admin/users.php]
- Table view of users. Actions: Block, Unblock, Delete (Soft delete preferrably).
#### [NEW] [admin/user_view.php]
- View specific user details and their booking history.

### 5. Flight Management
#### [NEW] [admin/flights.php]
- List existing flights.
#### [NEW] [admin/flight_form.php]
- Single form for Add/Edit flight. Handles POST requests to insert/update `flights` table.

### 6. Hotel Management
#### [NEW] [admin/hotels.php]
- List hotels.
#### [NEW] [admin/hotel_form.php]
- Add/Edit hotel details and room types.

### 7. Tour Management
#### [NEW] [admin/tours.php]
- List tour packages.
#### [NEW] [admin/tour_form.php]
- Add/Edit packages.

### 8. Booking & Payment Management
#### [NEW] [admin/bookings.php]
- Master list of all bookings. Filters: Status, Date, Type.
- Action: Approve/Cancel.
#### [NEW] [admin/payments.php]
- View payment logs.

### 9. Reviews & Notifications
#### [NEW] [admin/reviews.php]
- List pending reviews -> Approve/Delete.
#### [NEW] [admin/notifications.php]
- Form to send broadcast emails/notifications.

## Verification Plan
1. **Setup**: Run `admin/setup_admin_db.php`.
2. **Auth**: Login as admin. Try to access dashboard without login.
3. **CRUD Testing**:
    - Add a User, Block them.
    - Add a Flight, Edit it.
    - Add a Hotel, Check DB.
    - Add a Tour.
4. **Booking Flow**: Simulate a booking record in DB, check if it appears in Admin Panel. Change status.
