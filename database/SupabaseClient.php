<?php
// ===================================================================
// TripNexus - Supabase PHP Client
// Communicates with Supabase REST API (PostgREST)
// Endpoint: https://jtsuchakskithnoohjup.supabase.co/rest/v1/
// ===================================================================

require_once __DIR__ . '/supabase_config.php';

class SupabaseClient {
    private string $baseUrl;
    private string $apiKey;

    public function __construct(?string $baseUrl = null, ?string $apiKey = null) {
        $this->baseUrl = rtrim($baseUrl ?? SUPABASE_REST_URL, '/') . '/';
        $this->apiKey = $apiKey ?? SUPABASE_API_KEY;
    }

    public function from(string $table): SupabaseQueryBuilder {
        return new SupabaseQueryBuilder($this->baseUrl, $this->apiKey, $table);
    }

    public function getBaseUrl(): string {
        return $this->baseUrl;
    }

    public function getApiKey(): string {
        return $this->apiKey;
    }
}

class SupabaseQueryBuilder {
    private string $baseUrl;
    private string $apiKey;
    private string $table;
    private array $queryParams = [];
    private array $headers = [];
    private string $method = 'GET';
    private ?string $bodyPayload = null;

    public function __construct(string $baseUrl, string $apiKey, string $table) {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
        $this->table = $table;
        
        $this->headers = [
            'apikey: ' . $this->apiKey,
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ];
    }

    public function select(string $columns = '*'): self {
        $this->method = 'GET';
        $this->queryParams['select'] = $columns;
        return $this;
    }

    public function insert(array $data): self {
        $this->method = 'POST';
        $this->bodyPayload = json_encode($data);
        return $this;
    }

    public function update(array $data): self {
        $this->method = 'PATCH';
        $this->bodyPayload = json_encode($data);
        return $this;
    }

    public function delete(): self {
        $this->method = 'DELETE';
        return $this;
    }

    public function eq(string $column, $value): self {
        $this->queryParams[$column] = 'eq.' . $value;
        return $this;
    }

    public function neq(string $column, $value): self {
        $this->queryParams[$column] = 'neq.' . $value;
        return $this;
    }

    public function gt(string $column, $value): self {
        $this->queryParams[$column] = 'gt.' . $value;
        return $this;
    }

    public function gte(string $column, $value): self {
        $this->queryParams[$column] = 'gte.' . $value;
        return $this;
    }

    public function lt(string $column, $value): self {
        $this->queryParams[$column] = 'lt.' . $value;
        return $this;
    }

    public function lte(string $column, $value): self {
        $this->queryParams[$column] = 'lte.' . $value;
        return $this;
    }

    public function like(string $column, string $pattern): self {
        $this->queryParams[$column] = 'like.' . $pattern;
        return $this;
    }

    public function ilike(string $column, string $pattern): self {
        $this->queryParams[$column] = 'ilike.' . $pattern;
        return $this;
    }

    public function in(string $column, array $values): self {
        $this->queryParams[$column] = 'in.(' . implode(',', array_map(fn($v) => '"' . addslashes($v) . '"', $values)) . ')';
        return $this;
    }

    public function order(string $column, string $direction = 'asc'): self {
        $this->queryParams['order'] = $column . '.' . strtolower($direction);
        return $this;
    }

    public function limit(int $count): self {
        $this->queryParams['limit'] = $count;
        return $this;
    }

    public function offset(int $count): self {
        $this->queryParams['offset'] = $count;
        return $this;
    }

    public function execute(): array {
        $url = $this->baseUrl . rawurlencode($this->table);
        if (!empty($this->queryParams)) {
            $url .= '?' . http_build_query($this->queryParams);
        }

        $opts = [
            'http' => [
                'method'  => $this->method,
                'header'  => implode("\r\n", $this->headers),
                'ignore_errors' => true,
                'timeout' => 15
            ]
        ];

        if ($this->bodyPayload !== null) {
            $opts['http']['content'] = $this->bodyPayload;
        }

        $context = stream_context_create($opts);
        $response = @file_get_contents($url, false, $context);
        
        $statusCode = 500;
        if (isset($http_response_header) && count($http_response_header) > 0) {
            preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $matches);
            if (isset($matches[1])) {
                $statusCode = (int)$matches[1];
            }
        }

        if ($response === false) {
            return [
                'success' => false,
                'status' => $statusCode,
                'error' => 'Network error connecting to Supabase REST API',
                'data' => []
            ];
        }

        $decoded = json_decode($response, true);
        if ($statusCode >= 200 && $statusCode < 300) {
            return [
                'success' => true,
                'status' => $statusCode,
                'data' => is_array($decoded) ? $decoded : []
            ];
        }

        return [
            'success' => false,
            'status' => $statusCode,
            'error' => $decoded['message'] ?? $decoded['hint'] ?? $response,
            'data' => []
        ];
    }
}
?>
