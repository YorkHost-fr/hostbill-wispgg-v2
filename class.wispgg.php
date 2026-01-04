<?php

// ============================================
// DEBUG CONFIGURATION
// ============================================
// Set to true to enable detailed debug logging to /tmp/wisp_debug.log
define('WISP_DEBUG_ENABLED', true);

// Debug log file path (can be changed if needed)
define('WISP_DEBUG_LOG', '/tmp/wisp_debug.log');
// ============================================

/**
 * Class wispgg
 *
 * Hosting/Provisioning module - Optimized version with pagination
 *
 * @see http://dev.hostbillapp.com/dev-kit/provisioning-modules/
 * @author Xephia.eu
 *
 */
class wispgg extends HostingModule {

    use \Components\Traits\LoggerTrait;

    protected $_repository = 'hosting_wispgg';
    protected $version = '1.0.1';
    protected $modname = 'Wisp.gg';
    protected $description = 'Wisp.gg module for HostBill - Optimized';
    protected $db;

    protected $serverFields = [
        self::CONNECTION_FIELD_USERNAME => false,
        self::CONNECTION_FIELD_PASSWORD => false,
        self::CONNECTION_FIELD_INPUT1 => true,
        self::CONNECTION_FIELD_INPUT2 => false,
        self::CONNECTION_FIELD_CHECKBOX => true,
        self::CONNECTION_FIELD_HOSTNAME => true,
        self::CONNECTION_FIELD_IPADDRESS => false,
        self::CONNECTION_FIELD_MAXACCOUNTS => false,
        self::CONNECTION_FIELD_STATUSURL => false,
        self::CONNECTION_FIELD_TEXTAREA => false,
    ];

    protected $serverFieldsDescription = [
        self::CONNECTION_FIELD_INPUT1 => 'Api Application Key',
    ];

    protected $options = [
        'CPU' => [
            'value' => '',
            'description' => 'The amount of cpu limit you want the server to have',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'cpu',
            '_tab' => 'resources',
        ],
        'Disk Space' => [
            'value' => '',
            'description' => 'The amount of storage you want the server to use',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'disk',
            '_tab' => 'resources',
        ],
        'Disk Space Unit' => [
            'value' => 'MB',
            'description' => 'Unit for disk size set',
            'type' => 'select',
            'default' => ['MB','GB'],
            '_tab' => 'resources',
        ],
        'Memory' => [
            'value' => '',
            'description' => 'The amount of memory you want the server to use',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'memory',
            '_tab' => 'resources',
        ],
        'Memory Space Unit' => [
            'value' => 'MB',
            'description' => 'Unit for memory/swap size set',
            'type' => 'select',
            'default' => ['MB','GB'],
            '_tab' => 'resources',
        ],
        'Swap' => [
            'value' => '',
            'description' => 'The amount of swap memory',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'swap',
            '_tab' => 'resources',
        ],
        'Block IO Weight' => [
            'value' => '',
            'description' => 'Block IO Weight',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'block_io_weight',
            '_tab' => 'resources',
        ],
        'Databases' => [
            'value' => '',
            'description' => 'The total number of databases allowed',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'database',
            '_tab' => 'resources',
        ],
        'Dedicated IP' => [
            'value' => '',
            'description' => 'Check if you want the server to have a dedicated IP',
            'type' => 'check',
            'default' => '',
            'forms' => 'checkbox',
            'variable' => 'dedicated',
            '_tab' => 'resources',
        ],
        'Allocations' => [
            'value' => '',
            'description' => 'Number of allocations allowed',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'allocation',
            '_tab' => 'resources',
        ],
        'Backups' => [
            'value' => '',
            'description' => 'The server\'s backups limit (in GB)',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'backups',
            '_tab' => 'resources',
        ],
        'Location' => [
            'value' => '',
            'description' => 'Locations that nodes can be assigned',
            'type' => 'loadable',
            'default' => 'getLocations',
            'forms' => 'select',
            'variable' => 'location',
            '_tab' => 'resources',
        ],
        'Port Range' => [
            'value' => '',
            'description' => 'Port range filter (e.g., 30100-30200). Leave empty to disable filtering.',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'port_range',
            '_tab' => 'resources',
        ],
        'Nest' => [
            'value' => '',
            'description' => 'Select the Nest',
            'type' => 'loadable',
            'default' => 'getNests',
            'forms' => 'select',
            'variable' => 'nest',
            '_tab' => 'nest',
        ],
        'Egg' => [
            'value' => '',
            'description' => 'Select the Egg',
            'type' => 'loadable',
            'default' => 'getEggs',
            'forms' => 'select',
            'variable' => 'egg',
            '_tab' => 'nest',
        ],
        'Egg variables' => [
            'value' => '',
            'description' => 'Egg variables (e.g. variable:value;)',
            'type' => 'textarea',
            'default' => '',
            'forms' => 'input',
            'variable' => 'egg_variable',
            '_tab' => 'nest',
        ],
        'Docker Image' => [
            'value' => '',
            'description' => 'Docker image to use',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'docker_image',
            '_tab' => 'nest',
        ],
        'Startup script' => [
            'value' => '',
            'description' => 'Startup command',
            'type' => 'textarea',
            'default' => '',
            'forms' => 'input',
            'variable' => 'startup_script',
            '_tab' => 'nest',
        ],
        'Data Pack' => [
            'value' => '',
            'description' => '',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'data_pack',
            '_tab' => 'nest',
        ],
    ];

    protected $details = [
        'device_id' => [
            'name' => 'device_id',
            'value' => false,
            'type' => 'input',
            'default' => false
        ],
        'username' => [
            'name' => 'username',
            'value' => false,
            'type' => 'input',
            'default' => false
        ],
        'password' => [
            'name' => 'password',
            'value' => false,
            'type' => 'input',
            'default' => false
        ],
        'domain' => [
            'name' => 'domain',
            'value' => false,
            'type' => 'input',
            'default' => false
        ],
    ];

    private $hostname;
    private $api_key;
    private $secure;
    private $response;
    private $response_code;

    /**
     * Debug logging helper method
     */
    private function debugLog($message) {
        if (defined('WISP_DEBUG_ENABLED') && WISP_DEBUG_ENABLED) {
            $log_file = defined('WISP_DEBUG_LOG') ? WISP_DEBUG_LOG : '/tmp/wisp_debug.log';
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] " . $message . "\n", FILE_APPEND);
        }
    }

    public function connect($connect) {
        $this->hostname = $connect['host'];
        $this->api_key = $connect['field1'];
        $this->secure = $connect['secure'];
    }

    public function testConnection() {
        $check = $this->api('users');
        return $check !== false;
    }

    function _parseHostname() {
        $hostname = $this->hostname;
        if (ip2long($hostname) !== false) $hostname = 'http://' . $hostname;
        else $hostname = ($this->secure ? 'https://' : 'http://') . $hostname;
        return rtrim($hostname, '/');
    }

    function api($endpoint, $method = "GET", $data = [], $ignoreErrors = []) {
        $url = $this->_parseHostname() . '/api/admin/' . $endpoint;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        $post = json_encode($data);
        $headers = [
            "Authorization: Bearer " . $this->api_key,
            "Accept: Application/vnd.pterodactyl.v1+json",
            "Content-Type: application/json",
        ];
        if ($method === 'POST' || $method === 'PATCH' || $method === 'PUT') {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
            $headers[] = "Content-Length: " . strlen($post);
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $this->logger()->debug('HB ==> Wisp', [
            'url' => $url,
            'method' => $method,
            'data' => $post
        ]);

        $result = curl_exec($curl);
        $response = $this->response = json_decode($result, true);
        $code = $this->response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);
        curl_close($curl);

        // Store endpoint and method for error reporting (like old version)
        $error_context = [
            'endpoint' => $endpoint,
            'method' => $method,
            'data' => $post
        ];

        $this->logger()->debug('HB <== Wisp', [
            'code' => $code,
            'response' => $response
        ]);

        // Log to file only if debug is enabled
        $this->debugLog("API Response Code: {$code}");
        $this->debugLog("API Response: " . json_encode($response, JSON_PRETTY_PRINT));

        if ($err) {
            $this->debugLog("cURL Error: {$err}");
            // Show error like old version - just the connection error
            $this->addError('Connection error: ' . $err);
            return false;
        } else if (isset($response['errors'])) {
            $this->debugLog("API Errors: " . json_encode($response['errors'], JSON_PRETTY_PRINT));

            // Show errors like old version - multiple lines with context
            $hasErrors = false;
            foreach ($response['errors'] as $error) {
                if (in_array($error['code'], $ignoreErrors)) continue;

                $errorCode = $error['code'] ?? 'UNKNOWN_ERROR';
                $errorDetail = $error['detail'] ?? 'Unknown error';

                // Add error details (like old version)
                $this->addError($errorCode . ' details: ' . $errorDetail);

                // Add source if available
                if (isset($error['source']['pointer'])) {
                    $this->addError('Field: ' . $error['source']['pointer']);
                }

                // Add context information (like old version)
                $this->addError('Endpoint: ' . $error_context['endpoint']);
                $this->addError('Method: ' . $error_context['method']);

                // Add data only if debug is enabled (to avoid cluttering in production)
                if (defined('WISP_DEBUG_ENABLED') && WISP_DEBUG_ENABLED) {
                    $this->addError('Data: ' . $error_context['data']);
                }

                $hasErrors = true;
            }

            if ($hasErrors) {
                return false;
            }

            return $response;
        } else if ($code >= 400) {
            // Handle HTTP error codes without detailed error messages (like old version - multiple lines)
            $this->debugLog("HTTP Error {$code}");

            // Main error message
            if ($code == 400) {
                $this->addError('HTTP 400: Bad Request - Invalid parameters sent to API');
            } else if ($code == 401) {
                $this->addError('HTTP 401: Unauthorized - Check your API key');
            } else if ($code == 403) {
                $this->addError('HTTP 403: Forbidden - API key does not have required permissions');
            } else if ($code == 404) {
                $this->addError('HTTP 404: Not Found - Resource does not exist');
            } else if ($code == 422) {
                $this->addError('HTTP 422: Unprocessable Entity - Validation failed');
            } else if ($code == 429) {
                $this->addError('HTTP 429: Too Many Requests - Rate limit exceeded');
            } else if ($code >= 500) {
                $this->addError('HTTP ' . $code . ': Server Error - Wisp.gg panel is experiencing issues');
            } else {
                $this->addError('HTTP Error ' . $code);
            }

            // Add context (like old version)
            $this->addError('Endpoint: ' . $error_context['endpoint']);
            $this->addError('Method: ' . $error_context['method']);

            // Add response body if available and debug enabled
            if (defined('WISP_DEBUG_ENABLED') && WISP_DEBUG_ENABLED) {
                if (is_string($result) && !empty($result)) {
                    $this->addError('Response: ' . substr($result, 0, 200));
                }
                $this->addError('Data: ' . $error_context['data']);
            }

            return false;
        }

        return $response;
    }

    public function Create() {
        $this->debugLog("\n========== CREATE SERVER START ==========");

        $egg = $this->getEgg($this->resource('nest'), $this->resource('egg'));
        $this->debugLog("Egg retrieved: " . json_encode($egg));

        $user = $this->getOrCreateUser();
        if (!$user) {
            $this->debugLog("ERROR: Cannot create user");
            $this->addError('Cannot create user - Please check user credentials and API permissions');
            return false;
        }
        $this->debugLog("User ID: {$user}");

        $mult_disk = $this->options['Disk Space Unit']['value'] == 'GB' ? 1000 : 1;
        $mult_mem = $this->options['Memory Space Unit']['value'] == 'GB' ? 1024 : 1;
        $mult_backups = 1000;

        $data = [];
        $data['oom_disabled'] = false;
        $data['owner_id'] = $user;
        $data['external_id'] = $this->account_details["id"];
        $data['name'] = 'Merci YorkHost.FR !';
        $data['egg_id'] = $this->resource('egg');

        // Docker image with fallback to egg's docker image
        $configured_docker_image = $this->resource('docker_image');

        // Get egg's docker image - handle both 'docker_image' (string) and 'docker_images' (object)
        $egg_docker_image = '';
        if (isset($egg['docker_image']) && !empty($egg['docker_image'])) {
            $egg_docker_image = $egg['docker_image'];
        } elseif (isset($egg['docker_images']) && is_array($egg['docker_images']) && !empty($egg['docker_images'])) {
            // Get first docker image from docker_images object
            $egg_docker_image = reset($egg['docker_images']);
        }

        // Use configured docker image if not empty, otherwise use egg's docker image
        if (!empty($configured_docker_image)) {
            $data['docker_image'] = $configured_docker_image;
        } else {
            $data['docker_image'] = $egg_docker_image;
        }

        $this->debugLog("Docker image (configured): " . var_export($configured_docker_image, true));
        $this->debugLog("Docker image (egg default): " . var_export($egg_docker_image, true));
        $this->debugLog("Docker image (final): " . var_export($data['docker_image'], true));

        // Startup with fallback to egg's startup command
        $configured_startup = $this->resource('startup_script');
        $egg_startup = $egg['startup'] ?? '';
        $data['startup'] = !empty($configured_startup) ? $configured_startup : $egg_startup;
        $data['memory'] = (int)$this->resource('memory') * $mult_mem;
        $data['swap'] = (int)$this->resource('swap') * $mult_mem;
        $data['disk'] = (int)$this->resource('disk') * $mult_disk;

        // Set IO with default value if not specified or invalid
        $io_weight = (int)$this->resource('block_io_weight');
        $data['io'] = ($io_weight >= 10 && $io_weight <= 1000) ? $io_weight : 500;

        $data['force_outgoing_ip'] = true;
        $data['cpu'] = (int)$this->resource('cpu');
        $data['database_limit'] = (int)$this->resource('database');
        $data['allocation_limit'] = (int)$this->resource('allocation') ?: null;
        $data['backup_megabytes_limit'] = (int)$this->resource('backups') * $mult_backups;

        $variables = $this->resource('egg_variable');
        if (!$variables) {
            $this->debugLog("ERROR: Wrong or empty Egg variables");
            $this->addError('Wrong or empty Egg variables - Please configure egg variables in product settings');
            return false;
        }
        $this->debugLog("Egg variables: {$variables}");

        $nodeAndAllocations = $this->getNodeAndAllocations();
        if (!$nodeAndAllocations) {
            $this->debugLog("ERROR: No suitable nodes with allocations");
            $this->addError('No suitable nodes with allocations available - Please check node configuration and port availability');
            return false;
        }

        $data["node_id"] = $nodeAndAllocations["node"];
        $data["primary_allocation_id"] = $nodeAndAllocations["primary_allocation_id"][0];
        if (isset($nodeAndAllocations["secondary_allocation_ids"])) {
            foreach ($nodeAndAllocations["secondary_allocation_ids"] as $idAndPort) {
                $data["secondary_allocations_ids"][] = $idAndPort[0];
            }
        }
        $data['start_on_completion'] = true;
        $data = $this->parseVariables($variables, $nodeAndAllocations, $data);

        $this->debugLog("Final server data: " . json_encode($data, JSON_PRETTY_PRINT));

        $server = $this->api('servers', 'POST', $data);
        if (is_array($server) && isset($server['attributes']['id'])) {
            $this->details['device_id']['value'] = $server['attributes']['id'];
            $this->debugLog("SUCCESS: Server created with ID: {$server['attributes']['id']}");
            $this->debugLog("========== CREATE SERVER END ==========\n");
            return true;
        }

        $this->debugLog("ERROR: Failed to create server");
        $this->debugLog("API Response: " . json_encode($server));
        $this->debugLog("========== CREATE SERVER END ==========\n");
        $this->addError('Failed to create server - Check debug log for detailed error information');
        return false;
    }

    /**
     * Get node and allocations with pagination and optional port filtering
     */
public function getNodeAndAllocations() {
    $allocation_count = (int)$this->resource('allocation') + 1;
    $port_range = $this->resource('port_range');

    // Static node ID
    $node_id = 91;

    $this->debugLog("Starting allocation search");
    $this->debugLog("Node ID: {$node_id}");
    $this->debugLog("Allocation count needed: {$allocation_count}");

    // Get node IP for ip_port filtering
    $node_ip = $this->getNodeIP($node_id);
    if (!$node_ip) {
        $this->debugLog("ERROR: Cannot get node IP");
        $this->addError('Cannot get node IP - Node may not exist or have no allocations configured');
        return false;
    }
    $this->debugLog("Node IP: {$node_ip}");

    // Parse port range and extract prefix
    $allowed_ports = [];
    $port_prefix = '';
    if (!empty($port_range)) {
        $allowed_ports = $this->parsePortRange($port_range);
        $port_prefix = $this->extractPortPrefix($port_range);
        $this->debugLog("Port range: {$port_range}");
        $this->debugLog("Port prefix: {$port_prefix}");
        $this->debugLog("Allowed ports count: " . count($allowed_ports));
        
        // Try with filters first
        $selected = $this->findAllocationsWithPagination(
            $node_id,
            $allocation_count,
            $node_ip,
            $port_prefix,
            $allowed_ports
        );
        
        if ($selected) {
            $this->debugLog("Allocations found with port filtering");
            return $selected;
        }
        
        $this->debugLog("No allocations found with port filtering, trying without filters...");
    } else {
        $this->debugLog("No port filtering configured");
    }

    // Fallback: try without any IP/port filters
    $this->debugLog("Attempting fallback: searching all available allocations on node");
    $selected = $this->findAllocationsWithPagination(
        $node_id,
        $allocation_count,
        null,  // No IP filter
        '',    // No port prefix
        []     // No port restrictions
    );

    if ($selected) {
        $this->debugLog("Allocations found successfully (fallback mode)");
        $this->debugLog("Result: " . json_encode($selected));
        return $selected;
    }

    $this->debugLog("ERROR: No allocations available even without filters");
    $this->addError('No allocations available - All ports on this node are in use');
    return false;
}

    /**
     * Get node IP address - just grab first allocation IP
     */
    private function getNodeIP($node_id) {
        $this->debugLog("Fetching node IP...");

        $allocations = $this->api("nodes/{$node_id}/allocations?per_page=1");

        $this->debugLog("Allocations response: " . json_encode($allocations));

        if ($allocations && isset($allocations['data']) && !empty($allocations['data'])) {
            $ip = $allocations['data'][0]['attributes']['ip'];
            $this->debugLog("Node IP from first allocation: {$ip}");
            return $ip;
        }

        $this->debugLog("ERROR: No allocations found to get IP");
        return false;
    }

    /**
     * Find allocations with pagination support using filter[ip_port]
     */
    private function findAllocationsWithPagination($node_id, $needed_count, $node_ip, $port_prefix, $allowed_ports) {
        $selected_allocations = [];
        $page = 1;
        $per_page = 50;
        $max_pages = 20; // Safety limit

        $this->debugLog("=== Starting pagination loop ===");

        while (count($selected_allocations) < $needed_count && $page <= $max_pages) {
            // Build URL with filter[ip_port] in format IP:PORT - this works 100%
            $url = "nodes/{$node_id}/allocations?filter[in_use]=false&per_page={$per_page}&page={$page}";

            // Use filter[ip_port]=IP:PORT_PREFIX (e.g., 83.150.218.137:30)
        if ($node_ip && !empty($port_prefix)) {
            $url .= "&filter[ip_port]={$node_ip}:{$port_prefix}";
        } else if ($node_ip) {
            $url .= "&filter[ip_port]={$node_ip}";
        }

            $this->debugLog("Page {$page}: Fetching URL: {$url}");

            $response = $this->api($url);
            if (!$response || !isset($response['data']) || empty($response['data'])) {
                $this->debugLog("Page {$page}: No data received, stopping");
                break; // No more data
            }

            $received_count = count($response['data']);
            $this->debugLog("Page {$page}: Received {$received_count} allocations");

            $page_selected = 0;
            $page_rejected = 0;

            foreach ($response['data'] as $allocation) {
                $port = $allocation['attributes']['port'];
                $alloc_id = $allocation['attributes']['id'];

                // Validate against exact port range if specified
                if (!empty($allowed_ports) && !in_array($port, $allowed_ports)) {
                    $page_rejected++;
                    $this->debugLog("Page {$page}: Rejected allocation ID {$alloc_id} (port {$port} not in allowed range)");
                    continue;
                }

                $selected_allocations[] = [
                    'id' => $alloc_id,
                    'port' => $port
                ];
                $page_selected++;
                $this->debugLog("Page {$page}: Selected allocation ID {$alloc_id} (port {$port})");

                if (count($selected_allocations) >= $needed_count) {
                    $this->debugLog("Page {$page}: Got enough allocations ({$needed_count}), stopping");
                    break 2; // Got enough
                }
            }

            $this->debugLog("Page {$page}: Selected {$page_selected}, Rejected {$page_rejected}");
            $this->debugLog("Page {$page}: Total selected so far: " . count($selected_allocations) . "/{$needed_count}");

            // Check pagination
            if (isset($response['meta']['pagination'])) {
                $pagination = $response['meta']['pagination'];
                $this->debugLog("Page {$page}: Pagination: current={$pagination['current_page']}, total={$pagination['total_pages']}, count={$pagination['count']}, total_count={$pagination['total']}");
                if ($pagination['current_page'] >= $pagination['total_pages']) {
                    $this->debugLog("Page {$page}: Last page reached");
                    break; // Last page
                }
            } else {
                $this->debugLog("Page {$page}: No pagination info, stopping");
                break; // No pagination info
            }

            $page++;
        }

        $this->debugLog("=== Pagination loop finished ===");
        $this->debugLog("Final count: " . count($selected_allocations) . "/{$needed_count}");

        if (count($selected_allocations) < $needed_count) {
            $this->debugLog("ERROR: Not enough allocations found");
            $this->logger()->warning('Not enough allocations', [
                'node' => $node_id,
                'needed' => $needed_count,
                'found' => count($selected_allocations)
            ]);
            return false;
        }

        // Build result
        $result = ['node' => $node_id];
        foreach ($selected_allocations as $index => $alloc) {
            if ($index === 0) {
                $result['primary_allocation_id'] = [$alloc['id'], $alloc['port']];
            } else {
                $result['secondary_allocation_ids'][] = [$alloc['id'], $alloc['port']];
            }
        }

        $this->debugLog("Final result built: " . json_encode($result));
        return $result;
    }

    /**
     * Extract port prefix from range
     */
    private function extractPortPrefix($port_range) {
        if (strpos($port_range, '-') !== false) {
            list($start, $end) = explode('-', trim($port_range), 2);
            $start = trim($start);
            $end = trim($end);

            // Find common prefix
            $prefix = '';
            $minLen = min(strlen($start), strlen($end));
            for ($i = 0; $i < $minLen; $i++) {
                if ($start[$i] === $end[$i]) {
                    $prefix .= $start[$i];
                } else {
                    break;
                }
            }
            return $prefix;
        }

        // For comma-separated or single port, use first 3 chars
        $first_port = explode(',', $port_range)[0];
        return substr(trim($first_port), 0, 3);
    }

    /**
     * Parse port range to array
     */
    private function parsePortRange($port_range) {
        if (empty($port_range)) return [];

        // Range: "30100-30200"
        if (strpos($port_range, '-') !== false) {
            list($start, $end) = array_map('intval', explode('-', trim($port_range), 2));
            if ($start > 0 && $end >= $start) {
                return range($start, $end);
            }
        }
        // List: "30100,30101,30102"
        elseif (strpos($port_range, ',') !== false) {
            $ports = array_map('intval', array_map('trim', explode(',', $port_range)));
            return array_filter($ports, function($p) { return $p > 0; });
        }
        // Single: "30100"
        else {
            $port = intval(trim($port_range));
            if ($port > 0) return [$port];
        }

        return [];
    }

    public function getOrCreateUser() {
        $user = $this->getUser($this->client_data['id']);
        if (!$user) {
            return $this->createUser();
        }

        $q = $this->db->prepare("SELECT id, username, password FROM hb_accounts WHERE client_id = :client_id AND server_id = :server_id LIMIT 1");
        $q->execute([':client_id' => $this->client_data['id'], ':server_id' => $this->account_details['server_id']]);
        $ret = $q->fetch(PDO::FETCH_ASSOC);
        $q->closeCursor();

        if ($ret) {
            $this->details['username']['value'] = $ret['username'];
            $this->details['password']['value'] = Utilities::decrypt($ret['password']);
        }

        return $user['attributes']['id'];
    }

    private function createUser() {
        $userResult = $this->api('users?filter[email]=' . urlencode($this->client_data['email']));

        if ($userResult['meta']['pagination']['total'] === 0) {
            $language = $this->client_data["language"] ?? "english";
            $wisp_language = ($language === "czech") ? "cs_CZ" : "en";

            $userResult = $this->api('users', 'POST', [
                'external_id' => $this->client_data['id'],
                'username' => $this->details['username']['value'],
                'password' => $this->details['password']['value'],
                'email' => $this->client_data['email'],
                'name_first' => $this->client_data['firstname'],
                'name_last' => $this->client_data['lastname'] ?: $this->client_data['firstname'],
                'preferences' => ["language" => $wisp_language]
            ]);
        } else {
            $userResult = $userResult['data'][0];
        }

        if (in_array($this->response_code, [200, 201])) {
            return $userResult['attributes']['id'];
        }

        // Add detailed error like old version
        $this->addError('Failed to create user');
        if (isset($userResult['status_code'])) {
            $this->addError('Received error code: ' . $userResult['status_code']);
        }
        if (isset($this->response_code)) {
            $this->addError('HTTP Response code: ' . $this->response_code);
        }
        return false;
    }

    public function getUser($client_id) {
        $user = $this->api('users/external/' . $client_id, "GET", [], ["NotFoundHttpException"]);
        return ($this->response_code === 404) ? false : $user;
    }

    public function getLocations() {
        $locations = $this->api('locations');
        if (!$locations) return false;

        $locations_array = [];
        foreach ($locations['data'] as $location) {
            $locations_array[] = [$location['attributes']['id'], $location['attributes']['long']];
        }
        return $locations_array;
    }

    public function getNests() {
        $nests = $this->api('nests');
        if (!$nests) return false;

        $nests_array = [];
        foreach ($nests['data'] as $nest) {
            $nests_array[] = [$nest['attributes']['id'], $nest['attributes']['name']];
        }
        return $nests_array;
    }

    public function getEggs() {
        $eggs_array = [];
        try {
            $r = RequestHandler::singleton();
            $products = new Products();
            $product = $products->getProduct($r->getParam('id'));
            if ($product['options']['Nest']) {
                $eggs = $this->api('nests/' . $product['options']['Nest'] . '/eggs');
                if ($eggs) {
                    foreach ($eggs['data'] as $egg) {
                        $eggs_array[] = [$egg['attributes']['id'], 'Egg ' . $egg['attributes']['id']];
                    }
                }
            }
        } catch (Exception $e) {}
        return $eggs_array;
    }

    public function getEgg($nest_id, $egg_id) {
        $egg = $this->api('nests/' . $nest_id . '/eggs/' . $egg_id);
        return $egg ? $egg['attributes'] : false;
    }

    public function getServerDetails() {
        $details = $this->api('servers/' . $this->details['device_id']['value'] . '?include[]=node&include[]=nest&include[]=egg&include[]=allocations&include[]=user&include[]=features');
        return $details['attributes'];
    }

    public function Suspend() {
        $this->api('servers/' . $this->account_details['extra_details']['device_id'] . '/suspension', 'POST', ['suspended' => true]);
        return in_array($this->response_code, [200, 204]);
    }

    public function Unsuspend() {
        $this->api('servers/' . $this->account_details['extra_details']['device_id'] . '/suspension', 'POST', ['suspended' => false]);
        return in_array($this->response_code, [200, 204]);
    }

    public function Reinstall() {
        $this->api('servers/' . $this->account_details['extra_details']['device_id'] . '/reinstall', 'POST');
        return in_array($this->response_code, [200, 204]);
    }

    public function Rebuild() {
        $this->api('servers/' . $this->account_details['extra_details']['device_id'] . '/rebuild', 'POST');
        return in_array($this->response_code, [200, 204]);
    }

    public function Terminate() {
        $this->api('servers/' . $this->account_details['extra_details']['device_id'], 'DELETE');
        return in_array($this->response_code, [200, 204]);
    }

    public function ChangePackage() {
        $serv_details = $this->getServerDetails();
        $allocations = $serv_details['relationships']['allocations']['data'];
        $mult_disk = $this->options['Disk Space Unit']['value'] == 'GB' ? 1000 : 1;
        $mult_mem = $this->options['Memory Space Unit']['value'] == 'GB' ? 1024 : 1;
        $mult_backups = 1000;

        // Set IO with default value if not specified or invalid
        $io_weight = (int)$this->resource('block_io_weight');
        $io_value = ($io_weight >= 10 && $io_weight <= 1000) ? $io_weight : 500;

        $this->api('servers/' . $this->account_details['extra_details']['device_id'] . '/build', 'PUT', [
            'allocation_id' => $this->getPrimaryAllocation($allocations),
            'memory' => (int)$this->resource('memory') * $mult_mem,
            'swap' => (int)$this->resource('swap') * $mult_mem,
            'disk' => (int)$this->resource('disk') * $mult_disk,
            'io' => $io_value,
            'cpu' => (int)$this->resource('cpu'),
            'database_limit' => (int)$this->resource('database'),
            'allocation_limit' => (int)$this->resource('allocation'),
            'backup_megabytes_limit' => (int)$this->resource('backups') * $mult_backups,
        ]);

        if (!in_array($this->response_code, [200, 204])) return false;

        $egg = $this->getEgg($this->resource('nest'), $this->resource('egg'));
        $nodeAndAllocations = $this->getNodeAndAllocations();
        if (!$nodeAndAllocations) return false;

        // Docker image with fallback to egg's docker image
        $configured_docker_image = $this->resource('docker_image');

        // Get egg's docker image - handle both 'docker_image' (string) and 'docker_images' (object)
        $egg_docker_image = '';
        if (isset($egg['docker_image']) && !empty($egg['docker_image'])) {
            $egg_docker_image = $egg['docker_image'];
        } elseif (isset($egg['docker_images']) && is_array($egg['docker_images']) && !empty($egg['docker_images'])) {
            // Get first docker image from docker_images object
            $egg_docker_image = reset($egg['docker_images']);
        }

        $docker_image = !empty($configured_docker_image) ? $configured_docker_image : $egg_docker_image;

        // Startup with fallback to egg's startup command
        $configured_startup = $this->resource('startup_script');
        $egg_startup = $egg['startup'] ?? '';
        $startup = !empty($configured_startup) ? $configured_startup : $egg_startup;

        $data = [
            'egg_id' => $this->resource('egg'),
            'startup' => $startup,
            'docker_image' => $docker_image,
            'skip_scripts' => false
        ];
        $data = $this->parseVariables($this->resource('egg_variable'), $nodeAndAllocations, $data);

        $this->api('servers/' . $this->account_details['extra_details']['device_id'] . '/startup', 'PUT', $data);
        return in_array($this->response_code, [200, 204]);
    }

    public function getPrimaryAllocation($allocations) {
        foreach ($allocations as $allocation) {
            if ($allocation['attributes']['primary']) {
                return $allocation['attributes']['id'];
            }
        }
        return false;
    }

    public function changeFormsFields($account_config) {
        if (empty($account_config)) return true;
        $this->setAccountConfig(array_merge($this->account_config, $account_config));
        return $this->ChangePackage();
    }

    public function getPanelLoginUrl() {
        return $this->_parseHostname() . '/login';
    }

    public function getSynchInfo() {
        $info = $this->getServerDetails();
        $this->details['domain']['value'] = $info['name'];
        $this->options['Memory']['value'] = $info['limits']['memory'];
        return ['suspended' => $info['suspended']];
    }

    public function getProductServers($product_id) {
        if (empty($product_id)) return false;
        $query = $this->db->prepare("SELECT `server` FROM hb_products_modules WHERE `product_id` = :product_id");
        $query->execute(['product_id' => $product_id]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        $query->closeCursor();
        return $result ? explode(',', $result['server']) : false;
    }

    public function getAccounts() {
        $return = [];
        try {
            $servers = $this->api('servers/?include=user');
            foreach ($servers['data'] as $server) {
                $server = $server['attributes'];
                $user = $server['relationships']['user']['attributes'];
                $return[] = [
                    'email' => $user['email'],
                    'username' => $user['username'],
                    'domain' => $server['name'],
                    'status' => $server['suspended'],
                    'extra_details' => [
                        'device_id' => $server['id'],
                        'domain' => $server['name']
                    ]
                ];
            }
        } catch (Exception $e) {
            $this->logger()->error('Wisp error', ['message' => $e->getMessage()]);
        }
        return $return;
    }

    public function getImportType() {
        return ImportAccounts_Model::TYPE_IMPORT_NO_PRODUCTS;
    }

    function parseVariables($variables, $nodeAndAllocations, $data) {
        $nextAllocation = 0;
        $env = explode(';', $variables);
        foreach ($env as $ev) {
            $e = explode(':', $ev);
            if (isset($e[1])) {
                $val = trim($e[1]);
                preg_match_all("/\\\$([a-zA-Z_{}]*)/", $val, $match);
                foreach ($match[1] as $item) {
                    if ($item === '{allocation}') {
                        if (isset($nodeAndAllocations["secondary_allocation_ids"][$nextAllocation])) {
                            $val = (string)$nodeAndAllocations["secondary_allocation_ids"][$nextAllocation][1];
                            $nextAllocation++;
                        }
                    } else if ($item === '{port}') {
                        $val = (string)$nodeAndAllocations["primary_allocation_id"][1];
                    } else {
                        if (isset($this->account_config[$item])) {
                            $val = $this->account_config[$item]["variable_id"] ?? $this->account_config[$item]["value"] ?? $val;
                        }
                    }
                }
                $data['environment'][trim($e[0])] = $val;
            }
        }
        return $data;
    }
}
[root@YH-HB-CLIENT-BK1 wispgg]# cat class.wispgg.php^C
[root@YH-HB-CLIENT-BK1 wispgg]# ^C
[root@YH-HB-CLIENT-BK1 wispgg]# ls
admin  class.wispgg.php  class.wispgg.php.bkp  class.wispgg.php.lastmaj  class.wispgg.php.lastmaj18112025
[root@YH-HB-CLIENT-BK1 wispgg]# nano class.wispgg.php
[root@YH-HB-CLIENT-BK1 wispgg]# rm class.wispgg.php.lastmaj
rm: remove regular file 'class.wispgg.php.lastmaj'? y
[root@YH-HB-CLIENT-BK1 wispgg]# rm class.wispgg.php.
class.wispgg.php.bkp              class.wispgg.php.lastmaj18112025  
[root@YH-HB-CLIENT-BK1 wispgg]# rm class.wispgg.php.
class.wispgg.php.bkp              class.wispgg.php.lastmaj18112025  
[root@YH-HB-CLIENT-BK1 wispgg]# rm class.wispgg.php.lastmaj18112025 
rm: remove regular file 'class.wispgg.php.lastmaj18112025'? y
[root@YH-HB-CLIENT-BK1 wispgg]# ls
admin  class.wispgg.php  class.wispgg.php.bkp
[root@YH-HB-CLIENT-BK1 wispgg]# rm class.wispgg.php.bkp 
rm: remove regular file 'class.wispgg.php.bkp'? y 
[root@YH-HB-CLIENT-BK1 wispgg]# ls
admin  class.wispgg.php
[root@YH-HB-CLIENT-BK1 wispgg]# cat class.wispgg.php class.wispgg.php.bkp
<?php

// ============================================
// DEBUG CONFIGURATION
// ============================================
// Set to true to enable detailed debug logging to /tmp/wisp_debug.log
define('WISP_DEBUG_ENABLED', true);

// Debug log file path (can be changed if needed)
define('WISP_DEBUG_LOG', '/tmp/wisp_debug.log');
// ============================================

/**
 * Class wispgg
 *
 * Hosting/Provisioning module - Optimized version with pagination
 *
 * @see http://dev.hostbillapp.com/dev-kit/provisioning-modules/
 * @author Xephia.eu
 *
 */
class wispgg extends HostingModule {

    use \Components\Traits\LoggerTrait;

    protected $_repository = 'hosting_wispgg';
    protected $version = '1.0.1';
    protected $modname = 'Wisp.gg';
    protected $description = 'Wisp.gg module for HostBill - Optimized';
    protected $db;

    protected $serverFields = [
        self::CONNECTION_FIELD_USERNAME => false,
        self::CONNECTION_FIELD_PASSWORD => false,
        self::CONNECTION_FIELD_INPUT1 => true,
        self::CONNECTION_FIELD_INPUT2 => false,
        self::CONNECTION_FIELD_CHECKBOX => true,
        self::CONNECTION_FIELD_HOSTNAME => true,
        self::CONNECTION_FIELD_IPADDRESS => false,
        self::CONNECTION_FIELD_MAXACCOUNTS => false,
        self::CONNECTION_FIELD_STATUSURL => false,
        self::CONNECTION_FIELD_TEXTAREA => false,
    ];

    protected $serverFieldsDescription = [
        self::CONNECTION_FIELD_INPUT1 => 'Api Application Key',
    ];

    protected $options = [
        'CPU' => [
            'value' => '',
            'description' => 'The amount of cpu limit you want the server to have',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'cpu',
            '_tab' => 'resources',
        ],
        'Disk Space' => [
            'value' => '',
            'description' => 'The amount of storage you want the server to use',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'disk',
            '_tab' => 'resources',
        ],
        'Disk Space Unit' => [
            'value' => 'MB',
            'description' => 'Unit for disk size set',
            'type' => 'select',
            'default' => ['MB','GB'],
            '_tab' => 'resources',
        ],
        'Memory' => [
            'value' => '',
            'description' => 'The amount of memory you want the server to use',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'memory',
            '_tab' => 'resources',
        ],
        'Memory Space Unit' => [
            'value' => 'MB',
            'description' => 'Unit for memory/swap size set',
            'type' => 'select',
            'default' => ['MB','GB'],
            '_tab' => 'resources',
        ],
        'Swap' => [
            'value' => '',
            'description' => 'The amount of swap memory',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'swap',
            '_tab' => 'resources',
        ],
        'Block IO Weight' => [
            'value' => '',
            'description' => 'Block IO Weight',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'block_io_weight',
            '_tab' => 'resources',
        ],
        'Databases' => [
            'value' => '',
            'description' => 'The total number of databases allowed',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'database',
            '_tab' => 'resources',
        ],
        'Dedicated IP' => [
            'value' => '',
            'description' => 'Check if you want the server to have a dedicated IP',
            'type' => 'check',
            'default' => '',
            'forms' => 'checkbox',
            'variable' => 'dedicated',
            '_tab' => 'resources',
        ],
        'Allocations' => [
            'value' => '',
            'description' => 'Number of allocations allowed',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'allocation',
            '_tab' => 'resources',
        ],
        'Backups' => [
            'value' => '',
            'description' => 'The server\'s backups limit (in GB)',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'backups',
            '_tab' => 'resources',
        ],
        'Location' => [
            'value' => '',
            'description' => 'Locations that nodes can be assigned',
            'type' => 'loadable',
            'default' => 'getLocations',
            'forms' => 'select',
            'variable' => 'location',
            '_tab' => 'resources',
        ],
        'Port Range' => [
            'value' => '',
            'description' => 'Port range filter (e.g., 30100-30200). Leave empty to disable filtering.',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'port_range',
            '_tab' => 'resources',
        ],
        'Nest' => [
            'value' => '',
            'description' => 'Select the Nest',
            'type' => 'loadable',
            'default' => 'getNests',
            'forms' => 'select',
            'variable' => 'nest',
            '_tab' => 'nest',
        ],
        'Egg' => [
            'value' => '',
            'description' => 'Select the Egg',
            'type' => 'loadable',
            'default' => 'getEggs',
            'forms' => 'select',
            'variable' => 'egg',
            '_tab' => 'nest',
        ],
        'Egg variables' => [
            'value' => '',
            'description' => 'Egg variables (e.g. variable:value;)',
            'type' => 'textarea',
            'default' => '',
            'forms' => 'input',
            'variable' => 'egg_variable',
            '_tab' => 'nest',
        ],
        'Docker Image' => [
            'value' => '',
            'description' => 'Docker image to use',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'docker_image',
            '_tab' => 'nest',
        ],
        'Startup script' => [
            'value' => '',
            'description' => 'Startup command',
            'type' => 'textarea',
            'default' => '',
            'forms' => 'input',
            'variable' => 'startup_script',
            '_tab' => 'nest',
        ],
        'Data Pack' => [
            'value' => '',
            'description' => '',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'data_pack',
            '_tab' => 'nest',
        ],
    ];

    protected $details = [
        'device_id' => [
            'name' => 'device_id',
            'value' => false,
            'type' => 'input',
            'default' => false
        ],
        'username' => [
            'name' => 'username',
            'value' => false,
            'type' => 'input',
            'default' => false
        ],
        'password' => [
            'name' => 'password',
            'value' => false,
            'type' => 'input',
            'default' => false
        ],
        'domain' => [
            'name' => 'domain',
            'value' => false,
            'type' => 'input',
            'default' => false
        ],
    ];

    private $hostname;
    private $api_key;
    private $secure;
    private $response;
    private $response_code;

    /**
     * Debug logging helper method
     */
    private function debugLog($message) {
        if (defined('WISP_DEBUG_ENABLED') && WISP_DEBUG_ENABLED) {
            $log_file = defined('WISP_DEBUG_LOG') ? WISP_DEBUG_LOG : '/tmp/wisp_debug.log';
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] " . $message . "\n", FILE_APPEND);
        }
    }

    public function connect($connect) {
        $this->hostname = $connect['host'];
        $this->api_key = $connect['field1'];
        $this->secure = $connect['secure'];
    }

    public function testConnection() {
        $check = $this->api('users');
        return $check !== false;
    }

    function _parseHostname() {
        $hostname = $this->hostname;
        if (ip2long($hostname) !== false) $hostname = 'http://' . $hostname;
        else $hostname = ($this->secure ? 'https://' : 'http://') . $hostname;
        return rtrim($hostname, '/');
    }

    function api($endpoint, $method = "GET", $data = [], $ignoreErrors = []) {
        $url = $this->_parseHostname() . '/api/admin/' . $endpoint;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        $post = json_encode($data);
        $headers = [
            "Authorization: Bearer " . $this->api_key,
            "Accept: Application/vnd.pterodactyl.v1+json",
            "Content-Type: application/json",
        ];
        if ($method === 'POST' || $method === 'PATCH' || $method === 'PUT') {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
            $headers[] = "Content-Length: " . strlen($post);
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $this->logger()->debug('HB ==> Wisp', [
            'url' => $url,
            'method' => $method,
            'data' => $post
        ]);

        $result = curl_exec($curl);
        $response = $this->response = json_decode($result, true);
        $code = $this->response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);
        curl_close($curl);

        // Store endpoint and method for error reporting (like old version)
        $error_context = [
            'endpoint' => $endpoint,
            'method' => $method,
            'data' => $post
        ];

        $this->logger()->debug('HB <== Wisp', [
            'code' => $code,
            'response' => $response
        ]);

        // Log to file only if debug is enabled
        $this->debugLog("API Response Code: {$code}");
        $this->debugLog("API Response: " . json_encode($response, JSON_PRETTY_PRINT));

        if ($err) {
            $this->debugLog("cURL Error: {$err}");
            // Show error like old version - just the connection error
            $this->addError('Connection error: ' . $err);
            return false;
        } else if (isset($response['errors'])) {
            $this->debugLog("API Errors: " . json_encode($response['errors'], JSON_PRETTY_PRINT));

            // Show errors like old version - multiple lines with context
            $hasErrors = false;
            foreach ($response['errors'] as $error) {
                if (in_array($error['code'], $ignoreErrors)) continue;

                $errorCode = $error['code'] ?? 'UNKNOWN_ERROR';
                $errorDetail = $error['detail'] ?? 'Unknown error';

                // Add error details (like old version)
                $this->addError($errorCode . ' details: ' . $errorDetail);

                // Add source if available
                if (isset($error['source']['pointer'])) {
                    $this->addError('Field: ' . $error['source']['pointer']);
                }

                // Add context information (like old version)
                $this->addError('Endpoint: ' . $error_context['endpoint']);
                $this->addError('Method: ' . $error_context['method']);

                // Add data only if debug is enabled (to avoid cluttering in production)
                if (defined('WISP_DEBUG_ENABLED') && WISP_DEBUG_ENABLED) {
                    $this->addError('Data: ' . $error_context['data']);
                }

                $hasErrors = true;
            }

            if ($hasErrors) {
                return false;
            }

            return $response;
        } else if ($code >= 400) {
            // Handle HTTP error codes without detailed error messages (like old version - multiple lines)
            $this->debugLog("HTTP Error {$code}");

            // Main error message
            if ($code == 400) {
                $this->addError('HTTP 400: Bad Request - Invalid parameters sent to API');
            } else if ($code == 401) {
                $this->addError('HTTP 401: Unauthorized - Check your API key');
            } else if ($code == 403) {
                $this->addError('HTTP 403: Forbidden - API key does not have required permissions');
            } else if ($code == 404) {
                $this->addError('HTTP 404: Not Found - Resource does not exist');
            } else if ($code == 422) {
                $this->addError('HTTP 422: Unprocessable Entity - Validation failed');
            } else if ($code == 429) {
                $this->addError('HTTP 429: Too Many Requests - Rate limit exceeded');
            } else if ($code >= 500) {
                $this->addError('HTTP ' . $code . ': Server Error - Wisp.gg panel is experiencing issues');
            } else {
                $this->addError('HTTP Error ' . $code);
            }

            // Add context (like old version)
            $this->addError('Endpoint: ' . $error_context['endpoint']);
            $this->addError('Method: ' . $error_context['method']);

            // Add response body if available and debug enabled
            if (defined('WISP_DEBUG_ENABLED') && WISP_DEBUG_ENABLED) {
                if (is_string($result) && !empty($result)) {
                    $this->addError('Response: ' . substr($result, 0, 200));
                }
                $this->addError('Data: ' . $error_context['data']);
            }

            return false;
        }

        return $response;
    }

    public function Create() {
        $this->debugLog("\n========== CREATE SERVER START ==========");

        $egg = $this->getEgg($this->resource('nest'), $this->resource('egg'));
        $this->debugLog("Egg retrieved: " . json_encode($egg));

        $user = $this->getOrCreateUser();
        if (!$user) {
            $this->debugLog("ERROR: Cannot create user");
            $this->addError('Cannot create user - Please check user credentials and API permissions');
            return false;
        }
        $this->debugLog("User ID: {$user}");

        $mult_disk = $this->options['Disk Space Unit']['value'] == 'GB' ? 1000 : 1;
        $mult_mem = $this->options['Memory Space Unit']['value'] == 'GB' ? 1024 : 1;
        $mult_backups = 1000;

        $data = [];
        $data['oom_disabled'] = false;
        $data['owner_id'] = $user;
        $data['external_id'] = $this->account_details["id"];
        $data['name'] = 'Merci YorkHost.FR !';
        $data['egg_id'] = $this->resource('egg');

        // Docker image with fallback to egg's docker image
        $configured_docker_image = $this->resource('docker_image');

        // Get egg's docker image - handle both 'docker_image' (string) and 'docker_images' (object)
        $egg_docker_image = '';
        if (isset($egg['docker_image']) && !empty($egg['docker_image'])) {
            $egg_docker_image = $egg['docker_image'];
        } elseif (isset($egg['docker_images']) && is_array($egg['docker_images']) && !empty($egg['docker_images'])) {
            // Get first docker image from docker_images object
            $egg_docker_image = reset($egg['docker_images']);
        }

        // Use configured docker image if not empty, otherwise use egg's docker image
        if (!empty($configured_docker_image)) {
            $data['docker_image'] = $configured_docker_image;
        } else {
            $data['docker_image'] = $egg_docker_image;
        }

        $this->debugLog("Docker image (configured): " . var_export($configured_docker_image, true));
        $this->debugLog("Docker image (egg default): " . var_export($egg_docker_image, true));
        $this->debugLog("Docker image (final): " . var_export($data['docker_image'], true));

        // Startup with fallback to egg's startup command
        $configured_startup = $this->resource('startup_script');
        $egg_startup = $egg['startup'] ?? '';
        $data['startup'] = !empty($configured_startup) ? $configured_startup : $egg_startup;
        $data['memory'] = (int)$this->resource('memory') * $mult_mem;
        $data['swap'] = (int)$this->resource('swap') * $mult_mem;
        $data['disk'] = (int)$this->resource('disk') * $mult_disk;

        // Set IO with default value if not specified or invalid
        $io_weight = (int)$this->resource('block_io_weight');
        $data['io'] = ($io_weight >= 10 && $io_weight <= 1000) ? $io_weight : 500;

        $data['force_outgoing_ip'] = true;
        $data['cpu'] = (int)$this->resource('cpu');
        $data['database_limit'] = (int)$this->resource('database');
        $data['allocation_limit'] = (int)$this->resource('allocation') ?: null;
        $data['backup_megabytes_limit'] = (int)$this->resource('backups') * $mult_backups;

        $variables = $this->resource('egg_variable');
        if (!$variables) {
            $this->debugLog("ERROR: Wrong or empty Egg variables");
            $this->addError('Wrong or empty Egg variables - Please configure egg variables in product settings');
            return false;
        }
        $this->debugLog("Egg variables: {$variables}");

        $nodeAndAllocations = $this->getNodeAndAllocations();
        if (!$nodeAndAllocations) {
            $this->debugLog("ERROR: No suitable nodes with allocations");
            $this->addError('No suitable nodes with allocations available - Please check node configuration and port availability');
            return false;
        }

        $data["node_id"] = $nodeAndAllocations["node"];
        $data["primary_allocation_id"] = $nodeAndAllocations["primary_allocation_id"][0];
        if (isset($nodeAndAllocations["secondary_allocation_ids"])) {
            foreach ($nodeAndAllocations["secondary_allocation_ids"] as $idAndPort) {
                $data["secondary_allocations_ids"][] = $idAndPort[0];
            }
        }
        $data['start_on_completion'] = true;
        $data = $this->parseVariables($variables, $nodeAndAllocations, $data);

        $this->debugLog("Final server data: " . json_encode($data, JSON_PRETTY_PRINT));

        $server = $this->api('servers', 'POST', $data);
        if (is_array($server) && isset($server['attributes']['id'])) {
            $this->details['device_id']['value'] = $server['attributes']['id'];
            $this->debugLog("SUCCESS: Server created with ID: {$server['attributes']['id']}");
            $this->debugLog("========== CREATE SERVER END ==========\n");
            return true;
        }

        $this->debugLog("ERROR: Failed to create server");
        $this->debugLog("API Response: " . json_encode($server));
        $this->debugLog("========== CREATE SERVER END ==========\n");
        $this->addError('Failed to create server - Check debug log for detailed error information');
        return false;
    }

    /**
     * Get node and allocations with pagination and optional port filtering
     */
public function getNodeAndAllocations() {
    $allocation_count = (int)$this->resource('allocation') + 1;
    $port_range = $this->resource('port_range');

    // Static node ID
    $node_id = 91;

    $this->debugLog("Starting allocation search");
    $this->debugLog("Node ID: {$node_id}");
    $this->debugLog("Allocation count needed: {$allocation_count}");

    // Get node IP for ip_port filtering
    $node_ip = $this->getNodeIP($node_id);
    if (!$node_ip) {
        $this->debugLog("ERROR: Cannot get node IP");
        $this->addError('Cannot get node IP - Node may not exist or have no allocations configured');
        return false;
    }
    $this->debugLog("Node IP: {$node_ip}");

    // Parse port range and extract prefix
    $allowed_ports = [];
    $port_prefix = '';
    if (!empty($port_range)) {
        $allowed_ports = $this->parsePortRange($port_range);
        $port_prefix = $this->extractPortPrefix($port_range);
        $this->debugLog("Port range: {$port_range}");
        $this->debugLog("Port prefix: {$port_prefix}");
        $this->debugLog("Allowed ports count: " . count($allowed_ports));
        
        // Try with filters first
        $selected = $this->findAllocationsWithPagination(
            $node_id,
            $allocation_count,
            $node_ip,
            $port_prefix,
            $allowed_ports
        );
        
        if ($selected) {
            $this->debugLog("Allocations found with port filtering");
            return $selected;
        }
        
        $this->debugLog("No allocations found with port filtering, trying without filters...");
    } else {
        $this->debugLog("No port filtering configured");
    }

    // Fallback: try without any IP/port filters
    $this->debugLog("Attempting fallback: searching all available allocations on node");
    $selected = $this->findAllocationsWithPagination(
        $node_id,
        $allocation_count,
        null,  // No IP filter
        '',    // No port prefix
        []     // No port restrictions
    );

    if ($selected) {
        $this->debugLog("Allocations found successfully (fallback mode)");
        $this->debugLog("Result: " . json_encode($selected));
        return $selected;
    }

    $this->debugLog("ERROR: No allocations available even without filters");
    $this->addError('No allocations available - All ports on this node are in use');
    return false;
}

    /**
     * Get node IP address - just grab first allocation IP
     */
    private function getNodeIP($node_id) {
        $this->debugLog("Fetching node IP...");

        $allocations = $this->api("nodes/{$node_id}/allocations?per_page=1");

        $this->debugLog("Allocations response: " . json_encode($allocations));

        if ($allocations && isset($allocations['data']) && !empty($allocations['data'])) {
            $ip = $allocations['data'][0]['attributes']['ip'];
            $this->debugLog("Node IP from first allocation: {$ip}");
            return $ip;
        }

        $this->debugLog("ERROR: No allocations found to get IP");
        return false;
    }

    /**
     * Find allocations with pagination support using filter[ip_port]
     */
    private function findAllocationsWithPagination($node_id, $needed_count, $node_ip, $port_prefix, $allowed_ports) {
        $selected_allocations = [];
        $page = 1;
        $per_page = 50;
        $max_pages = 20; // Safety limit

        $this->debugLog("=== Starting pagination loop ===");

        while (count($selected_allocations) < $needed_count && $page <= $max_pages) {
            // Build URL with filter[ip_port] in format IP:PORT - this works 100%
            $url = "nodes/{$node_id}/allocations?filter[in_use]=false&per_page={$per_page}&page={$page}";

            // Use filter[ip_port]=IP:PORT_PREFIX (e.g., 83.150.218.137:30)
        if ($node_ip && !empty($port_prefix)) {
            $url .= "&filter[ip_port]={$node_ip}:{$port_prefix}";
        } else if ($node_ip) {
            $url .= "&filter[ip_port]={$node_ip}";
        }

            $this->debugLog("Page {$page}: Fetching URL: {$url}");

            $response = $this->api($url);
            if (!$response || !isset($response['data']) || empty($response['data'])) {
                $this->debugLog("Page {$page}: No data received, stopping");
                break; // No more data
            }

            $received_count = count($response['data']);
            $this->debugLog("Page {$page}: Received {$received_count} allocations");

            $page_selected = 0;
            $page_rejected = 0;

            foreach ($response['data'] as $allocation) {
                $port = $allocation['attributes']['port'];
                $alloc_id = $allocation['attributes']['id'];

                // Validate against exact port range if specified
                if (!empty($allowed_ports) && !in_array($port, $allowed_ports)) {
                    $page_rejected++;
                    $this->debugLog("Page {$page}: Rejected allocation ID {$alloc_id} (port {$port} not in allowed range)");
                    continue;
                }

                $selected_allocations[] = [
                    'id' => $alloc_id,
                    'port' => $port
                ];
                $page_selected++;
                $this->debugLog("Page {$page}: Selected allocation ID {$alloc_id} (port {$port})");

                if (count($selected_allocations) >= $needed_count) {
                    $this->debugLog("Page {$page}: Got enough allocations ({$needed_count}), stopping");
                    break 2; // Got enough
                }
            }

            $this->debugLog("Page {$page}: Selected {$page_selected}, Rejected {$page_rejected}");
            $this->debugLog("Page {$page}: Total selected so far: " . count($selected_allocations) . "/{$needed_count}");

            // Check pagination
            if (isset($response['meta']['pagination'])) {
                $pagination = $response['meta']['pagination'];
                $this->debugLog("Page {$page}: Pagination: current={$pagination['current_page']}, total={$pagination['total_pages']}, count={$pagination['count']}, total_count={$pagination['total']}");
                if ($pagination['current_page'] >= $pagination['total_pages']) {
                    $this->debugLog("Page {$page}: Last page reached");
                    break; // Last page
                }
            } else {
                $this->debugLog("Page {$page}: No pagination info, stopping");
                break; // No pagination info
            }

            $page++;
        }

        $this->debugLog("=== Pagination loop finished ===");
        $this->debugLog("Final count: " . count($selected_allocations) . "/{$needed_count}");

        if (count($selected_allocations) < $needed_count) {
            $this->debugLog("ERROR: Not enough allocations found");
            $this->logger()->warning('Not enough allocations', [
                'node' => $node_id,
                'needed' => $needed_count,
                'found' => count($selected_allocations)
            ]);
            return false;
        }

        // Build result
        $result = ['node' => $node_id];
        foreach ($selected_allocations as $index => $alloc) {
            if ($index === 0) {
                $result['primary_allocation_id'] = [$alloc['id'], $alloc['port']];
            } else {
                $result['secondary_allocation_ids'][] = [$alloc['id'], $alloc['port']];
            }
        }

        $this->debugLog("Final result built: " . json_encode($result));
        return $result;
    }

    /**
     * Extract port prefix from range
     */
    private function extractPortPrefix($port_range) {
        if (strpos($port_range, '-') !== false) {
            list($start, $end) = explode('-', trim($port_range), 2);
            $start = trim($start);
            $end = trim($end);

            // Find common prefix
            $prefix = '';
            $minLen = min(strlen($start), strlen($end));
            for ($i = 0; $i < $minLen; $i++) {
                if ($start[$i] === $end[$i]) {
                    $prefix .= $start[$i];
                } else {
                    break;
                }
            }
            return $prefix;
        }

        // For comma-separated or single port, use first 3 chars
        $first_port = explode(',', $port_range)[0];
        return substr(trim($first_port), 0, 3);
    }

    /**
     * Parse port range to array
     */
    private function parsePortRange($port_range) {
        if (empty($port_range)) return [];

        // Range: "30100-30200"
        if (strpos($port_range, '-') !== false) {
            list($start, $end) = array_map('intval', explode('-', trim($port_range), 2));
            if ($start > 0 && $end >= $start) {
                return range($start, $end);
            }
        }
        // List: "30100,30101,30102"
        elseif (strpos($port_range, ',') !== false) {
            $ports = array_map('intval', array_map('trim', explode(',', $port_range)));
            return array_filter($ports, function($p) { return $p > 0; });
        }
        // Single: "30100"
        else {
            $port = intval(trim($port_range));
            if ($port > 0) return [$port];
        }

        return [];
    }

    public function getOrCreateUser() {
        $user = $this->getUser($this->client_data['id']);
        if (!$user) {
            return $this->createUser();
        }

        $q = $this->db->prepare("SELECT id, username, password FROM hb_accounts WHERE client_id = :client_id AND server_id = :server_id LIMIT 1");
        $q->execute([':client_id' => $this->client_data['id'], ':server_id' => $this->account_details['server_id']]);
        $ret = $q->fetch(PDO::FETCH_ASSOC);
        $q->closeCursor();

        if ($ret) {
            $this->details['username']['value'] = $ret['username'];
            $this->details['password']['value'] = Utilities::decrypt($ret['password']);
        }

        return $user['attributes']['id'];
    }

    private function createUser() {
        $userResult = $this->api('users?filter[email]=' . urlencode($this->client_data['email']));

        if ($userResult['meta']['pagination']['total'] === 0) {
            $language = $this->client_data["language"] ?? "english";
            $wisp_language = ($language === "czech") ? "cs_CZ" : "en";

            $userResult = $this->api('users', 'POST', [
                'external_id' => $this->client_data['id'],
                'username' => $this->details['username']['value'],
                'password' => $this->details['password']['value'],
                'email' => $this->client_data['email'],
                'name_first' => $this->client_data['firstname'],
                'name_last' => $this->client_data['lastname'] ?: $this->client_data['firstname'],
                'preferences' => ["language" => $wisp_language]
            ]);
        } else {
            $userResult = $userResult['data'][0];
        }

        if (in_array($this->response_code, [200, 201])) {
            return $userResult['attributes']['id'];
        }

        // Add detailed error like old version
        $this->addError('Failed to create user');
        if (isset($userResult['status_code'])) {
            $this->addError('Received error code: ' . $userResult['status_code']);
        }
        if (isset($this->response_code)) {
            $this->addError('HTTP Response code: ' . $this->response_code);
        }
        return false;
    }

    public function getUser($client_id) {
        $user = $this->api('users/external/' . $client_id, "GET", [], ["NotFoundHttpException"]);
        return ($this->response_code === 404) ? false : $user;
    }

    public function getLocations() {
        $locations = $this->api('locations');
        if (!$locations) return false;

        $locations_array = [];
        foreach ($locations['data'] as $location) {
            $locations_array[] = [$location['attributes']['id'], $location['attributes']['long']];
        }
        return $locations_array;
    }

    public function getNests() {
        $nests = $this->api('nests');
        if (!$nests) return false;

        $nests_array = [];
        foreach ($nests['data'] as $nest) {
            $nests_array[] = [$nest['attributes']['id'], $nest['attributes']['name']];
        }
        return $nests_array;
    }

    public function getEggs() {
        $eggs_array = [];
        try {
            $r = RequestHandler::singleton();
            $products = new Products();
            $product = $products->getProduct($r->getParam('id'));
            if ($product['options']['Nest']) {
                $eggs = $this->api('nests/' . $product['options']['Nest'] . '/eggs');
                if ($eggs) {
                    foreach ($eggs['data'] as $egg) {
                        $eggs_array[] = [$egg['attributes']['id'], 'Egg ' . $egg['attributes']['id']];
                    }
                }
            }
        } catch (Exception $e) {}
        return $eggs_array;
    }

    public function getEgg($nest_id, $egg_id) {
        $egg = $this->api('nests/' . $nest_id . '/eggs/' . $egg_id);
        return $egg ? $egg['attributes'] : false;
    }

    public function getServerDetails() {
        $details = $this->api('servers/' . $this->details['device_id']['value'] . '?include[]=node&include[]=nest&include[]=egg&include[]=allocations&include[]=user&include[]=features');
        return $details['attributes'];
    }

    public function Suspend() {
        $this->api('servers/' . $this->account_details['extra_details']['device_id'] . '/suspension', 'POST', ['suspended' => true]);
        return in_array($this->response_code, [200, 204]);
    }

    public function Unsuspend() {
        $this->api('servers/' . $this->account_details['extra_details']['device_id'] . '/suspension', 'POST', ['suspended' => false]);
        return in_array($this->response_code, [200, 204]);
    }

    public function Reinstall() {
        $this->api('servers/' . $this->account_details['extra_details']['device_id'] . '/reinstall', 'POST');
        return in_array($this->response_code, [200, 204]);
    }

    public function Rebuild() {
        $this->api('servers/' . $this->account_details['extra_details']['device_id'] . '/rebuild', 'POST');
        return in_array($this->response_code, [200, 204]);
    }

    public function Terminate() {
        $this->api('servers/' . $this->account_details['extra_details']['device_id'], 'DELETE');
        return in_array($this->response_code, [200, 204]);
    }

    public function ChangePackage() {
        $serv_details = $this->getServerDetails();
        $allocations = $serv_details['relationships']['allocations']['data'];
        $mult_disk = $this->options['Disk Space Unit']['value'] == 'GB' ? 1000 : 1;
        $mult_mem = $this->options['Memory Space Unit']['value'] == 'GB' ? 1024 : 1;
        $mult_backups = 1000;

        // Set IO with default value if not specified or invalid
        $io_weight = (int)$this->resource('block_io_weight');
        $io_value = ($io_weight >= 10 && $io_weight <= 1000) ? $io_weight : 500;

        $this->api('servers/' . $this->account_details['extra_details']['device_id'] . '/build', 'PUT', [
            'allocation_id' => $this->getPrimaryAllocation($allocations),
            'memory' => (int)$this->resource('memory') * $mult_mem,
            'swap' => (int)$this->resource('swap') * $mult_mem,
            'disk' => (int)$this->resource('disk') * $mult_disk,
            'io' => $io_value,
            'cpu' => (int)$this->resource('cpu'),
            'database_limit' => (int)$this->resource('database'),
            'allocation_limit' => (int)$this->resource('allocation'),
            'backup_megabytes_limit' => (int)$this->resource('backups') * $mult_backups,
        ]);

        if (!in_array($this->response_code, [200, 204])) return false;

        $egg = $this->getEgg($this->resource('nest'), $this->resource('egg'));
        $nodeAndAllocations = $this->getNodeAndAllocations();
        if (!$nodeAndAllocations) return false;

        // Docker image with fallback to egg's docker image
        $configured_docker_image = $this->resource('docker_image');

        // Get egg's docker image - handle both 'docker_image' (string) and 'docker_images' (object)
        $egg_docker_image = '';
        if (isset($egg['docker_image']) && !empty($egg['docker_image'])) {
            $egg_docker_image = $egg['docker_image'];
        } elseif (isset($egg['docker_images']) && is_array($egg['docker_images']) && !empty($egg['docker_images'])) {
            // Get first docker image from docker_images object
            $egg_docker_image = reset($egg['docker_images']);
        }

        $docker_image = !empty($configured_docker_image) ? $configured_docker_image : $egg_docker_image;

        // Startup with fallback to egg's startup command
        $configured_startup = $this->resource('startup_script');
        $egg_startup = $egg['startup'] ?? '';
        $startup = !empty($configured_startup) ? $configured_startup : $egg_startup;

        $data = [
            'egg_id' => $this->resource('egg'),
            'startup' => $startup,
            'docker_image' => $docker_image,
            'skip_scripts' => false
        ];
        $data = $this->parseVariables($this->resource('egg_variable'), $nodeAndAllocations, $data);

        $this->api('servers/' . $this->account_details['extra_details']['device_id'] . '/startup', 'PUT', $data);
        return in_array($this->response_code, [200, 204]);
    }

    public function getPrimaryAllocation($allocations) {
        foreach ($allocations as $allocation) {
            if ($allocation['attributes']['primary']) {
                return $allocation['attributes']['id'];
            }
        }
        return false;
    }

    public function changeFormsFields($account_config) {
        if (empty($account_config)) return true;
        $this->setAccountConfig(array_merge($this->account_config, $account_config));
        return $this->ChangePackage();
    }

    public function getPanelLoginUrl() {
        return $this->_parseHostname() . '/login';
    }

    public function getSynchInfo() {
        $info = $this->getServerDetails();
        $this->details['domain']['value'] = $info['name'];
        $this->options['Memory']['value'] = $info['limits']['memory'];
        return ['suspended' => $info['suspended']];
    }

    public function getProductServers($product_id) {
        if (empty($product_id)) return false;
        $query = $this->db->prepare("SELECT `server` FROM hb_products_modules WHERE `product_id` = :product_id");
        $query->execute(['product_id' => $product_id]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        $query->closeCursor();
        return $result ? explode(',', $result['server']) : false;
    }

    public function getAccounts() {
        $return = [];
        try {
            $servers = $this->api('servers/?include=user');
            foreach ($servers['data'] as $server) {
                $server = $server['attributes'];
                $user = $server['relationships']['user']['attributes'];
                $return[] = [
                    'email' => $user['email'],
                    'username' => $user['username'],
                    'domain' => $server['name'],
                    'status' => $server['suspended'],
                    'extra_details' => [
                        'device_id' => $server['id'],
                        'domain' => $server['name']
                    ]
                ];
            }
        } catch (Exception $e) {
            $this->logger()->error('Wisp error', ['message' => $e->getMessage()]);
        }
        return $return;
    }

    public function getImportType() {
        return ImportAccounts_Model::TYPE_IMPORT_NO_PRODUCTS;
    }

    function parseVariables($variables, $nodeAndAllocations, $data) {
        $nextAllocation = 0;
        $env = explode(';', $variables);
        foreach ($env as $ev) {
            $e = explode(':', $ev);
            if (isset($e[1])) {
                $val = trim($e[1]);
                preg_match_all("/\\\$([a-zA-Z_{}]*)/", $val, $match);
                foreach ($match[1] as $item) {
                    if ($item === '{allocation}') {
                        if (isset($nodeAndAllocations["secondary_allocation_ids"][$nextAllocation])) {
                            $val = (string)$nodeAndAllocations["secondary_allocation_ids"][$nextAllocation][1];
                            $nextAllocation++;
                        }
                    } else if ($item === '{port}') {
                        $val = (string)$nodeAndAllocations["primary_allocation_id"][1];
                    } else {
                        if (isset($this->account_config[$item])) {
                            $val = $this->account_config[$item]["variable_id"] ?? $this->account_config[$item]["value"] ?? $val;
                        }
                    }
                }
                $data['environment'][trim($e[0])] = $val;
            }
        }
        return $data;
    }
}
cat: class.wispgg.php.bkp: No such file or directory
[root@YH-HB-CLIENT-BK1 wispgg]# cp class.wispgg.php class.wispgg.php.bkp
[root@YH-HB-CLIENT-BK1 wispgg]# rm class.wispgg.php
rm: remove regular file 'class.wispgg.php'? y
[root@YH-HB-CLIENT-BK1 wispgg]# nano class.wispgg.php
[root@YH-HB-CLIENT-BK1 wispgg]# git pull
fatal: detected dubious ownership in repository at '/home/hostbill/public_html/includes/modules/Hosting/wispgg'
To add an exception for this directory, call:

	git config --global --add safe.directory /home/hostbill/public_html/includes/modules/Hosting/wispgg
[root@YH-HB-CLIENT-BK1 wispgg]# git pull
fatal: detected dubious ownership in repository at '/home/hostbill/public_html/includes/modules/Hosting/wispgg'
To add an exception for this directory, call:

	git config --global --add safe.directory /home/hostbill/public_html/includes/modules/Hosting/wispgg
[root@YH-HB-CLIENT-BK1 wispgg]# ^C
[root@YH-HB-CLIENT-BK1 wispgg]# ls
admin  class.wispgg.php  class.wispgg.php.bkp
[root@YH-HB-CLIENT-BK1 wispgg]# nano class.wispgg.php
[root@YH-HB-CLIENT-BK1 wispgg]# cat class.wispgg.php
<?php

// ============================================
// DEBUG CONFIGURATION
// ============================================
// Set to true to enable detailed debug logging to /tmp/wisp_debug.log
define('WISP_DEBUG_ENABLED', true);

// Debug log file path (can be changed if needed)
define('WISP_DEBUG_LOG', '/tmp/wisp_debug.log');
// ============================================

/**
 * Class wispgg
 *
 * Hosting/Provisioning module - Optimized version with pagination
 * AUTO-FETCH: Automatically retrieves egg variables, startup, and docker image from Wisp.gg API
 *
 * @see http://dev.hostbillapp.com/dev-kit/provisioning-modules/
 * @author Xephia.eu
 *
 */
class wispgg extends HostingModule {

    use \Components\Traits\LoggerTrait;

    protected $_repository = 'hosting_wispgg';
    protected $version = '1.0.2';
    protected $modname = 'Wisp.gg';
    protected $description = 'Wisp.gg module for HostBill - Optimized with Auto-Fetch';
    protected $db;

    protected $serverFields = [
        self::CONNECTION_FIELD_USERNAME => false,
        self::CONNECTION_FIELD_PASSWORD => false,
        self::CONNECTION_FIELD_INPUT1 => true,
        self::CONNECTION_FIELD_INPUT2 => false,
        self::CONNECTION_FIELD_CHECKBOX => true,
        self::CONNECTION_FIELD_HOSTNAME => true,
        self::CONNECTION_FIELD_IPADDRESS => false,
        self::CONNECTION_FIELD_MAXACCOUNTS => false,
        self::CONNECTION_FIELD_STATUSURL => false,
        self::CONNECTION_FIELD_TEXTAREA => false,
    ];

    protected $serverFieldsDescription = [
        self::CONNECTION_FIELD_INPUT1 => 'Api Application Key',
    ];

    protected $options = [
        'CPU' => [
            'value' => '',
            'description' => 'The amount of cpu limit you want the server to have',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'cpu',
            '_tab' => 'resources',
        ],
        'Disk Space' => [
            'value' => '',
            'description' => 'The amount of storage you want the server to use',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'disk',
            '_tab' => 'resources',
        ],
        'Disk Space Unit' => [
            'value' => 'MB',
            'description' => 'Unit for disk size set',
            'type' => 'select',
            'default' => ['MB','GB'],
            '_tab' => 'resources',
        ],
        'Memory' => [
            'value' => '',
            'description' => 'The amount of memory you want the server to use',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'memory',
            '_tab' => 'resources',
        ],
        'Memory Space Unit' => [
            'value' => 'MB',
            'description' => 'Unit for memory/swap size set',
            'type' => 'select',
            'default' => ['MB','GB'],
            '_tab' => 'resources',
        ],
        'Swap' => [
            'value' => '',
            'description' => 'The amount of swap memory',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'swap',
            '_tab' => 'resources',
        ],
        'Block IO Weight' => [
            'value' => '',
            'description' => 'Block IO Weight',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'block_io_weight',
            '_tab' => 'resources',
        ],
        'Databases' => [
            'value' => '',
            'description' => 'The total number of databases allowed',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'database',
            '_tab' => 'resources',
        ],
        'Dedicated IP' => [
            'value' => '',
            'description' => 'Check if you want the server to have a dedicated IP',
            'type' => 'check',
            'default' => '',
            'forms' => 'checkbox',
            'variable' => 'dedicated',
            '_tab' => 'resources',
        ],
        'Allocations' => [
            'value' => '',
            'description' => 'Number of allocations allowed',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'allocation',
            '_tab' => 'resources',
        ],
        'Backups' => [
            'value' => '',
            'description' => 'The server\'s backups limit (in GB)',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'backups',
            '_tab' => 'resources',
        ],
        'Location' => [
            'value' => '',
            'description' => 'Locations that nodes can be assigned',
            'type' => 'loadable',
            'default' => 'getLocations',
            'forms' => 'select',
            'variable' => 'location',
            '_tab' => 'resources',
        ],
        'Port Range' => [
            'value' => '',
            'description' => 'Port range filter (e.g., 30100-30200). Leave empty to disable filtering.',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'port_range',
            '_tab' => 'resources',
        ],
        'Nest' => [
            'value' => '',
            'description' => 'Select the Nest',
            'type' => 'loadable',
            'default' => 'getNests',
            'forms' => 'select',
            'variable' => 'nest',
            '_tab' => 'nest',
        ],
        'Egg' => [
            'value' => '',
            'description' => 'Select the Egg',
            'type' => 'loadable',
            'default' => 'getEggs',
            'forms' => 'select',
            'variable' => 'egg',
            '_tab' => 'nest',
        ],
        'Egg variables' => [
            'value' => '',
            'description' => '[AUTO-FETCH] Egg variables will be fetched automatically from Wisp.gg. Manual override: variable:value;',
            'type' => 'textarea',
            'default' => '',
            'forms' => 'input',
            'variable' => 'egg_variable',
            '_tab' => 'nest',
        ],
        'Docker Image' => [
            'value' => '',
            'description' => '[AUTO-FETCH] Docker image will be fetched automatically from Wisp.gg. Leave empty to use egg default.',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'docker_image',
            '_tab' => 'nest',
        ],
        'Startup script' => [
            'value' => '',
            'description' => '[AUTO-FETCH] Startup command will be fetched automatically from Wisp.gg. Leave empty to use egg default.',
            'type' => 'textarea',
            'default' => '',
            'forms' => 'input',
            'variable' => 'startup_script',
            '_tab' => 'nest',
        ],
        'Data Pack' => [
            'value' => '',
            'description' => '',
            'type' => 'input',
            'default' => '',
            'forms' => 'input',
            'variable' => 'data_pack',
            '_tab' => 'nest',
        ],
    ];

    protected $details = [
        'device_id' => [
            'name' => 'device_id',
            'value' => false,
            'type' => 'input',
            'default' => false
        ],
        'username' => [
            'name' => 'username',
            'value' => false,
            'type' => 'input',
            'default' => false
        ],
        'password' => [
            'name' => 'password',
            'value' => false,
            'type' => 'input',
            'default' => false
        ],
        'domain' => [
            'name' => 'domain',
            'value' => false,
            'type' => 'input',
            'default' => false
        ],
    ];

    private $hostname;
    private $api_key;
    private $secure;
    private $response;
    private $response_code;

    /**
     * Debug logging helper method
     */
    private function debugLog($message) {
        if (defined('WISP_DEBUG_ENABLED') && WISP_DEBUG_ENABLED) {
            $log_file = defined('WISP_DEBUG_LOG') ? WISP_DEBUG_LOG : '/tmp/wisp_debug.log';
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] " . $message . "\n", FILE_APPEND);
        }
    }

    public function connect($connect) {
        $this->hostname = $connect['host'];
        $this->api_key = $connect['field1'];
        $this->secure = $connect['secure'];
    }

    public function testConnection() {
        $check = $this->api('users');
        return $check !== false;
    }

    function _parseHostname() {
        $hostname = $this->hostname;
        if (ip2long($hostname) !== false) $hostname = 'http://' . $hostname;
        else $hostname = ($this->secure ? 'https://' : 'http://') . $hostname;
        return rtrim($hostname, '/');
    }

    function api($endpoint, $method = "GET", $data = [], $ignoreErrors = []) {
        $url = $this->_parseHostname() . '/api/admin/' . $endpoint;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        $post = json_encode($data);
        $headers = [
            "Authorization: Bearer " . $this->api_key,
            "Accept: Application/vnd.pterodactyl.v1+json",
            "Content-Type: application/json",
        ];
        if ($method === 'POST' || $method === 'PATCH' || $method === 'PUT') {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
            $headers[] = "Content-Length: " . strlen($post);
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $this->logger()->debug('HB ==> Wisp', [
            'url' => $url,
            'method' => $method,
            'data' => $post
        ]);

        $result = curl_exec($curl);
        $response = $this->response = json_decode($result, true);
        $code = $this->response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);
        curl_close($curl);

        // Store endpoint and method for error reporting (like old version)
        $error_context = [
            'endpoint' => $endpoint,
            'method' => $method,
            'data' => $post
        ];

        $this->logger()->debug('HB <== Wisp', [
            'code' => $code,
            'response' => $response
        ]);

        // Log to file only if debug is enabled
        $this->debugLog("API Response Code: {$code}");
        $this->debugLog("API Response: " . json_encode($response, JSON_PRETTY_PRINT));

        if ($err) {
            $this->debugLog("cURL Error: {$err}");
            // Show error like old version - just the connection error
            $this->addError('Connection error: ' . $err);
            return false;
        } else if (isset($response['errors'])) {
            $this->debugLog("API Errors: " . json_encode($response['errors'], JSON_PRETTY_PRINT));

            // Show errors like old version - multiple lines with context
            $hasErrors = false;
            foreach ($response['errors'] as $error) {
                if (in_array($error['code'], $ignoreErrors)) continue;

                $errorCode = $error['code'] ?? 'UNKNOWN_ERROR';
                $errorDetail = $error['detail'] ?? 'Unknown error';

                // Add error details (like old version)
                $this->addError($errorCode . ' details: ' . $errorDetail);

                // Add source if available
                if (isset($error['source']['pointer'])) {
                    $this->addError('Field: ' . $error['source']['pointer']);
                }

                // Add context information (like old version)
                $this->addError('Endpoint: ' . $error_context['endpoint']);
                $this->addError('Method: ' . $error_context['method']);

                // Add data only if debug is enabled (to avoid cluttering in production)
                if (defined('WISP_DEBUG_ENABLED') && WISP_DEBUG_ENABLED) {
                    $this->addError('Data: ' . $error_context['data']);
                }

                $hasErrors = true;
            }

            if ($hasErrors) {
                return false;
            }

            return $response;
        } else if ($code >= 400) {
            // Handle HTTP error codes without detailed error messages (like old version - multiple lines)
            $this->debugLog("HTTP Error {$code}");

            // Main error message
            if ($code == 400) {
                $this->addError('HTTP 400: Bad Request - Invalid parameters sent to API');
            } else if ($code == 401) {
                $this->addError('HTTP 401: Unauthorized - Check your API key');
            } else if ($code == 403) {
                $this->addError('HTTP 403: Forbidden - API key does not have required permissions');
            } else if ($code == 404) {
                $this->addError('HTTP 404: Not Found - Resource does not exist');
            } else if ($code == 422) {
                $this->addError('HTTP 422: Unprocessable Entity - Validation failed');
            } else if ($code == 429) {
                $this->addError('HTTP 429: Too Many Requests - Rate limit exceeded');
            } else if ($code >= 500) {
                $this->addError('HTTP ' . $code . ': Server Error - Wisp.gg panel is experiencing issues');
            } else {
                $this->addError('HTTP Error ' . $code);
            }

            // Add context (like old version)
            $this->addError('Endpoint: ' . $error_context['endpoint']);
            $this->addError('Method: ' . $error_context['method']);

            // Add response body if available and debug enabled
            if (defined('WISP_DEBUG_ENABLED') && WISP_DEBUG_ENABLED) {
                if (is_string($result) && !empty($result)) {
                    $this->addError('Response: ' . substr($result, 0, 200));
                }
                $this->addError('Data: ' . $error_context['data']);
            }

            return false;
        }

        return $response;
    }

    /**
     * AUTO-FETCH: Build egg variables string from egg data
     * This automatically creates the variable:value; format from the egg's variables
     */
    private function buildEggVariablesFromEgg($egg) {
        $this->debugLog("[AUTO-FETCH] Building egg variables from API data");
        
        // Check if egg has relationships->variables
        if (!isset($egg['relationships']['variables']['data']) || !is_array($egg['relationships']['variables']['data'])) {
            $this->debugLog("[AUTO-FETCH] No variables found in egg data");
            return '';
        }
        
        $variables = [];
        foreach ($egg['relationships']['variables']['data'] as $var) {
            $varAttrs = $var['attributes'];
            $envVar = $varAttrs['env_variable'];
            $defaultValue = $varAttrs['default_value'] ?? '';
            
            $this->debugLog("[AUTO-FETCH] Found variable: {$envVar} = {$defaultValue}");
            $variables[] = "{$envVar}:{$defaultValue}";
        }
        
        $result = implode(';', $variables);
        $this->debugLog("[AUTO-FETCH] Built egg variables string: {$result}");
        
        return $result;
    }

    public function Create() {
        $this->debugLog("\n========== CREATE SERVER START ==========");

        // AUTO-FETCH: Get full egg data with includes
        $egg = $this->getEggWithIncludes($this->resource('nest'), $this->resource('egg'));
        if (!$egg) {
            $this->debugLog("ERROR: Cannot retrieve egg data");
            $this->addError('Cannot retrieve egg data from Wisp.gg - Please check Nest and Egg configuration');
            return false;
        }
        $this->debugLog("[AUTO-FETCH] Egg retrieved with full data: " . json_encode($egg));

        $user = $this->getOrCreateUser();
        if (!$user) {
            $this->debugLog("ERROR: Cannot create user");
            $this->addError('Cannot create user - Please check user credentials and API permissions');
            return false;
        }
        $this->debugLog("User ID: {$user}");

        $mult_disk = $this->options['Disk Space Unit']['value'] == 'GB' ? 1000 : 1;
        $mult_mem = $this->options['Memory Space Unit']['value'] == 'GB' ? 1024 : 1;
        $mult_backups = 1000;

        $data = [];
        $data['oom_disabled'] = false;
        $data['owner_id'] = $user;
        $data['external_id'] = $this->account_details["id"];
        $data['name'] = 'Merci YorkHost.FR !';
        $data['egg_id'] = $this->resource('egg');

        // AUTO-FETCH: Docker image - use configured value or fetch from egg
        $configured_docker_image = $this->resource('docker_image');
        
        // Get egg's docker image - handle both 'docker_image' (string) and 'docker_images' (object)
        $egg_docker_image = '';
        if (isset($egg['docker_image']) && !empty($egg['docker_image'])) {
            $egg_docker_image = $egg['docker_image'];
        } elseif (isset($egg['docker_images']) && is_array($egg['docker_images']) && !empty($egg['docker_images'])) {
            // Get first docker image from docker_images object
            $egg_docker_image = reset($egg['docker_images']);
        }

        // Use configured docker image if not empty, otherwise use egg's docker image (AUTO-FETCH)
        if (!empty($configured_docker_image)) {
            $data['docker_image'] = $configured_docker_image;
            $this->debugLog("[MANUAL] Using configured docker image: " . $configured_docker_image);
        } else {
            $data['docker_image'] = $egg_docker_image;
            $this->debugLog("[AUTO-FETCH] Using egg docker image: " . $egg_docker_image);
        }

        // AUTO-FETCH: Startup command - use configured value or fetch from egg
        $configured_startup = $this->resource('startup_script');
        $egg_startup = $egg['startup'] ?? '';
        
        if (!empty($configured_startup)) {
            $data['startup'] = $configured_startup;
            $this->debugLog("[MANUAL] Using configured startup: " . $configured_startup);
        } else {
            $data['startup'] = $egg_startup;
            $this->debugLog("[AUTO-FETCH] Using egg startup: " . $egg_startup);
        }
        
        $data['memory'] = (int)$this->resource('memory') * $mult_mem;
        $data['swap'] = (int)$this->resource('swap') * $mult_mem;
        $data['disk'] = (int)$this->resource('disk') * $mult_disk;

        // Set IO with default value if not specified or invalid
        $io_weight = (int)$this->resource('block_io_weight');
        $data['io'] = ($io_weight >= 10 && $io_weight <= 1000) ? $io_weight : 500;

        $data['force_outgoing_ip'] = true;
        $data['cpu'] = (int)$this->resource('cpu');
        $data['database_limit'] = (int)$this->resource('database');
        $data['allocation_limit'] = (int)$this->resource('allocation') ?: null;
        $data['backup_megabytes_limit'] = (int)$this->resource('backups') * $mult_backups;

        // AUTO-FETCH: Egg variables - use configured value or build from egg
        $configured_variables = $this->resource('egg_variable');
        
        if (!empty($configured_variables)) {
            // Use manually configured variables
            $variables = $configured_variables;
            $this->debugLog("[MANUAL] Using configured egg variables: {$variables}");
        } else {
            // AUTO-FETCH: Build variables from egg data
            $variables = $this->buildEggVariablesFromEgg($egg);
            if (empty($variables)) {
                $this->debugLog("WARNING: No egg variables configured or auto-fetched");
                // Don't fail, some eggs might not have variables
            } else {
                $this->debugLog("[AUTO-FETCH] Using auto-fetched egg variables: {$variables}");
            }
        }

        $nodeAndAllocations = $this->getNodeAndAllocations();
        if (!$nodeAndAllocations) {
            $this->debugLog("ERROR: No suitable nodes with allocations");
            $this->addError('No suitable nodes with allocations available - Please check node configuration and port availability');
            return false;
        }

        $data["node_id"] = $nodeAndAllocations["node"];
        $data["primary_allocation_id"] = $nodeAndAllocations["primary_allocation_id"][0];
        if (isset($nodeAndAllocations["secondary_allocation_ids"])) {
            foreach ($nodeAndAllocations["secondary_allocation_ids"] as $idAndPort) {
                $data["secondary_allocations_ids"][] = $idAndPort[0];
            }
        }
        $data['start_on_completion'] = true;
        
        // Parse variables only if we have them
        if (!empty($variables)) {
            $data = $this->parseVariables($variables, $nodeAndAllocations, $data);
        }

        $this->debugLog("Final server data: " . json_encode($data, JSON_PRETTY_PRINT));

        $server = $this->api('servers', 'POST', $data);
        if (is_array($server) && isset($server['attributes']['id'])) {
            $this->details['device_id']['value'] = $server['attributes']['id'];
            $this->debugLog("SUCCESS: Server created with ID: {$server['attributes']['id']}");
            $this->debugLog("========== CREATE SERVER END ==========\n");
            return true;
        }

        $this->debugLog("ERROR: Failed to create server");
        $this->debugLog("API Response: " . json_encode($server));
        $this->debugLog("========== CREATE SERVER END ==========\n");
        $this->addError('Failed to create server - Check debug log for detailed error information');
        return false;
    }

    /**
     * Get node and allocations with pagination and optional port filtering
     */
public function getNodeAndAllocations() {
    $allocation_count = (int)$this->resource('allocation') + 1;
    $port_range = $this->resource('port_range');

    // Static node ID
    $node_id = 91;

    $this->debugLog("Starting allocation search");
    $this->debugLog("Node ID: {$node_id}");
    $this->debugLog("Allocation count needed: {$allocation_count}");

    // Get node IP for ip_port filtering
    $node_ip = $this->getNodeIP($node_id);
    if (!$node_ip) {
        $this->debugLog("ERROR: Cannot get node IP");
        $this->addError('Cannot get node IP - Node may not exist or have no allocations configured');
        return false;
    }
    $this->debugLog("Node IP: {$node_ip}");

    // Parse port range and extract prefix
    $allowed_ports = [];
    $port_prefix = '';
    if (!empty($port_range)) {
        $allowed_ports = $this->parsePortRange($port_range);
        $port_prefix = $this->extractPortPrefix($port_range);
        $this->debugLog("Port range: {$port_range}");
        $this->debugLog("Port prefix: {$port_prefix}");
        $this->debugLog("Allowed ports count: " . count($allowed_ports));
        
        // Try with filters first
        $selected = $this->findAllocationsWithPagination(
            $node_id,
            $allocation_count,
            $node_ip,
            $port_prefix,
            $allowed_ports
        );
        
        if ($selected) {
            $this->debugLog("Allocations found with port filtering");
            return $selected;
        }
        
        $this->debugLog("No allocations found with port filtering, trying without filters...");
    } else {
        $this->debugLog("No port filtering configured");
    }

    // Fallback: try without any IP/port filters
    $this->debugLog("Attempting fallback: searching all available allocations on node");
    $selected = $this->findAllocationsWithPagination(
        $node_id,
        $allocation_count,
        null,  // No IP filter
        '',    // No port prefix
        []     // No port restrictions
    );

    if ($selected) {
        $this->debugLog("Allocations found successfully (fallback mode)");
        $this->debugLog("Result: " . json_encode($selected));
        return $selected;
    }

    $this->debugLog("ERROR: No allocations available even without filters");
    $this->addError('No allocations available - All ports on this node are in use');
    return false;
}

    /**
     * Get node IP address - just grab first allocation IP
     */
    private function getNodeIP($node_id) {
        $this->debugLog("Fetching node IP...");

        $allocations = $this->api("nodes/{$node_id}/allocations?per_page=1");

        $this->debugLog("Allocations response: " . json_encode($allocations));

        if ($allocations && isset($allocations['data']) && !empty($allocations['data'])) {
            $ip = $allocations['data'][0]['attributes']['ip'];
            $this->debugLog("Node IP from first allocation: {$ip}");
            return $ip;
        }

        $this->debugLog("ERROR: No allocations found to get IP");
        return false;
    }

    /**
     * Find allocations with pagination support using filter[ip_port]
     */
    private function findAllocationsWithPagination($node_id, $needed_count, $node_ip, $port_prefix, $allowed_ports) {
        $selected_allocations = [];
        $page = 1;
        $per_page = 50;
        $max_pages = 20; // Safety limit

        $this->debugLog("=== Starting pagination loop ===");

        while (count($selected_allocations) < $needed_count && $page <= $max_pages) {
            // Build URL with filter[ip_port] in format IP:PORT - this works 100%
            $url = "nodes/{$node_id}/allocations?filter[in_use]=false&per_page={$per_page}&page={$page}";

            // Use filter[ip_port]=IP:PORT_PREFIX (e.g., 83.150.218.137:30)
        if ($node_ip && !empty($port_prefix)) {
            $url .= "&filter[ip_port]={$node_ip}:{$port_prefix}";
        } else if ($node_ip) {
            $url .= "&filter[ip_port]={$node_ip}";
        }

            $this->debugLog("Page {$page}: Fetching URL: {$url}");

            $response = $this->api($url);
            if (!$response || !isset($response['data']) || empty($response['data'])) {
                $this->debugLog("Page {$page}: No data received, stopping");
                break; // No more data
            }

            $received_count = count($response['data']);
            $this->debugLog("Page {$page}: Received {$received_count} allocations");

            $page_selected = 0;
            $page_rejected = 0;

            foreach ($response['data'] as $allocation) {
                $port = $allocation['attributes']['port'];
                $alloc_id = $allocation['attributes']['id'];

                // Validate against exact port range if specified
                if (!empty($allowed_ports) && !in_array($port, $allowed_ports)) {
                    $page_rejected++;
                    $this->debugLog("Page {$page}: Rejected allocation ID {$alloc_id} (port {$port} not in allowed range)");
                    continue;
                }

                $selected_allocations[] = [
                    'id' => $alloc_id,
                    'port' => $port
                ];
                $page_selected++;
                $this->debugLog("Page {$page}: Selected allocation ID {$alloc_id} (port {$port})");

                if (count($selected_allocations) >= $needed_count) {
                    $this->debugLog("Page {$page}: Got enough allocations ({$needed_count}), stopping");
                    break 2; // Got enough
                }
            }

            $this->debugLog("Page {$page}: Selected {$page_selected}, Rejected {$page_rejected}");
            $this->debugLog("Page {$page}: Total selected so far: " . count($selected_allocations) . "/{$needed_count}");

            // Check pagination
            if (isset($response['meta']['pagination'])) {
                $pagination = $response['meta']['pagination'];
                $this->debugLog("Page {$page}: Pagination: current={$pagination['current_page']}, total={$pagination['total_pages']}, count={$pagination['count']}, total_count={$pagination['total']}");
                if ($pagination['current_page'] >= $pagination['total_pages']) {
                    $this->debugLog("Page {$page}: Last page reached");
                    break; // Last page
                }
            } else {
                $this->debugLog("Page {$page}: No pagination info, stopping");
                break; // No pagination info
            }

            $page++;
        }

        $this->debugLog("=== Pagination loop finished ===");
        $this->debugLog("Final count: " . count($selected_allocations) . "/{$needed_count}");

        if (count($selected_allocations) < $needed_count) {
            $this->debugLog("ERROR: Not enough allocations found");
            $this->logger()->warning('Not enough allocations', [
                'node' => $node_id,
                'needed' => $needed_count,
                'found' => count($selected_allocations)
            ]);
            return false;
        }

        // Build result
        $result = ['node' => $node_id];
        foreach ($selected_allocations as $index => $alloc) {
            if ($index === 0) {
                $result['primary_allocation_id'] = [$alloc['id'], $alloc['port']];
            } else {
                $result['secondary_allocation_ids'][] = [$alloc['id'], $alloc['port']];
            }
        }

        $this->debugLog("Final result built: " . json_encode($result));
        return $result;
    }

    /**
     * Extract port prefix from range
     */
    private function extractPortPrefix($port_range) {
        if (strpos($port_range, '-') !== false) {
            list($start, $end) = explode('-', trim($port_range), 2);
            $start = trim($start);
            $end = trim($end);

            // Find common prefix
            $prefix = '';
            $minLen = min(strlen($start), strlen($end));
            for ($i = 0; $i < $minLen; $i++) {
                if ($start[$i] === $end[$i]) {
                    $prefix .= $start[$i];
                } else {
                    break;
                }
            }
            return $prefix;
        }

        // For comma-separated or single port, use first 3 chars
        $first_port = explode(',', $port_range)[0];
        return substr(trim($first_port), 0, 3);
    }

    /**
     * Parse port range to array
     */
    private function parsePortRange($port_range) {
        if (empty($port_range)) return [];

        // Range: "30100-30200"
        if (strpos($port_range, '-') !== false) {
            list($start, $end) = array_map('intval', explode('-', trim($port_range), 2));
            if ($start > 0 && $end >= $start) {
                return range($start, $end);
            }
        }
        // List: "30100,30101,30102"
        elseif (strpos($port_range, ',') !== false) {
            $ports = array_map('intval', array_map('trim', explode(',', $port_range)));
            return array_filter($ports, function($p) { return $p > 0; });
        }
        // Single: "30100"
        else {
            $port = intval(trim($port_range));
            if ($port > 0) return [$port];
        }

        return [];
    }

    public function getOrCreateUser() {
        $user = $this->getUser($this->client_data['id']);
        if (!$user) {
            return $this->createUser();
        }

        $q = $this->db->prepare("SELECT id, username, password FROM hb_accounts WHERE client_id = :client_id AND server_id = :server_id LIMIT 1");
        $q->execute([':client_id' => $this->client_data['id'], ':server_id' => $this->account_details['server_id']]);
        $ret = $q->fetch(PDO::FETCH_ASSOC);
        $q->closeCursor();

        if ($ret) {
            $this->details['username']['value'] = $ret['username'];
            $this->details['password']['value'] = Utilities::decrypt($ret['password']);
        }

        return $user['attributes']['id'];
    }

    private function createUser() {
        $userResult = $this->api('users?filter[email]=' . urlencode($this->client_data['email']));

        if ($userResult['meta']['pagination']['total'] === 0) {
            $language = $this->client_data["language"] ?? "english";
            $wisp_language = ($language === "czech") ? "cs_CZ" : "en";

            $userResult = $this->api('users', 'POST', [
                'external_id' => $this->client_data['id'],
                'username' => $this->details['username']['value'],
                'password' => $this->details['password']['value'],
                'email' => $this->client_data['email'],
                'name_first' => $this->client_data['firstname'],
                'name_last' => $this->client_data['lastname'] ?: $this->client_data['firstname'],
                'preferences' => ["language" => $wisp_language]
            ]);
        } else {
            $userResult = $userResult['data'][0];
        }

        if (in_array($this->response_code, [200, 201])) {
            return $userResult['attributes']['id'];
        }

        // Add detailed error like old version
        $this->addError('Failed to create user');
        if (isset($userResult['status_code'])) {
            $this->addError('Received error code: ' . $userResult['status_code']);
        }
        if (isset($this->response_code)) {
            $this->addError('HTTP Response code: ' . $this->response_code);
        }
        return false;
    }

    public function getUser($client_id) {
        $user = $this->api('users/external/' . $client_id, "GET", [], ["NotFoundHttpException"]);
        return ($this->response_code === 404) ? false : $user;
    }

    public function getLocations() {
        $locations = $this->api('locations');
        if (!$locations) return false;

        $locations_array = [];
        foreach ($locations['data'] as $location) {
            $locations_array[] = [$location['attributes']['id'], $location['attributes']['long']];
        }
        return $locations_array;
    }

    public function getNests() {
        $nests = $this->api('nests');
        if (!$nests) return false;

        $nests_array = [];
        foreach ($nests['data'] as $nest) {
            $nests_array[] = [$nest['attributes']['id'], $nest['attributes']['name']];
        }
        return $nests_array;
    }

    public function getEggs() {
        $eggs_array = [];
        try {
            $r = RequestHandler::singleton();
            $products = new Products();
            $product = $products->getProduct($r->getParam('id'));
            if ($product['options']['Nest']) {
                $eggs = $this->api('nests/' . $product['options']['Nest'] . '/eggs');
                if ($eggs) {
                    foreach ($eggs['data'] as $egg) {
                        $eggs_array[] = [$egg['attributes']['id'], 'Egg ' . $egg['attributes']['id']];
                    }
                }
            }
        } catch (Exception $e) {}
        return $eggs_array;
    }

    /**
     * Get egg data (basic version - without includes)
     * Used for backward compatibility
     */
    public function getEgg($nest_id, $egg_id) {
        $egg = $this->api('nests/' . $nest_id . '/eggs/' . $egg_id);
        return $egg ? $egg['attributes'] : false;
    }

    /**
     * AUTO-FETCH: Get egg data with includes (variables, etc.)
     * This fetches complete egg configuration including all variables
     */
    public function getEggWithIncludes($nest_id, $egg_id) {
        $this->debugLog("[AUTO-FETCH] Fetching egg with includes: nest={$nest_id}, egg={$egg_id}");
        
        // Include variables in the API call
        $egg = $this->api('nests/' . $nest_id . '/eggs/' . $egg_id . '?include=variables');
        
        if (!$egg || !isset($egg['attributes'])) {
            $this->debugLog("[AUTO-FETCH] Failed to fetch egg data");
            return false;
        }
        
        $this->debugLog("[AUTO-FETCH] Successfully fetched egg: " . $egg['attributes']['name']);
        return $egg['attributes'];
    }

    public function getServerDetails() {
        $details = $this->api('servers/' . $this->details['device_id']['value'] . '?include[]=node&include[]=nest&include[]=egg&include[]=allocations&include[]=user&include[]=features');
        return $details['attributes'];
    }

    public function Suspend() {
        $this->api('servers/' . $this->account_details['extra_details']['device_id'] . '/suspension', 'POST', ['suspended' => true]);
        return in_array($this->response_code, [200, 204]);
    }

    public function Unsuspend() {
        $this->api('servers/' . $this->account_details['extra_details']['device_id'] . '/suspension', 'POST', ['suspended' => false]);
        return in_array($this->response_code, [200, 204]);
    }

    public function Reinstall() {
        $this->api('servers/' . $this->account_details['extra_details']['device_id'] . '/reinstall', 'POST');
        return in_array($this->response_code, [200, 204]);
    }

    public function Rebuild() {
        $this->api('servers/' . $this->account_details['extra_details']['device_id'] . '/rebuild', 'POST');
        return in_array($this->response_code, [200, 204]);
    }

    public function Terminate() {
        $this->api('servers/' . $this->account_details['extra_details']['device_id'], 'DELETE');
        return in_array($this->response_code, [200, 204]);
    }

    public function ChangePackage() {
        $serv_details = $this->getServerDetails();
        $allocations = $serv_details['relationships']['allocations']['data'];
        $mult_disk = $this->options['Disk Space Unit']['value'] == 'GB' ? 1000 : 1;
        $mult_mem = $this->options['Memory Space Unit']['value'] == 'GB' ? 1024 : 1;
        $mult_backups = 1000;

        // Set IO with default value if not specified or invalid
        $io_weight = (int)$this->resource('block_io_weight');
        $io_value = ($io_weight >= 10 && $io_weight <= 1000) ? $io_weight : 500;

        $this->api('servers/' . $this->account_details['extra_details']['device_id'] . '/build', 'PUT', [
            'allocation_id' => $this->getPrimaryAllocation($allocations),
            'memory' => (int)$this->resource('memory') * $mult_mem,
            'swap' => (int)$this->resource('swap') * $mult_mem,
            'disk' => (int)$this->resource('disk') * $mult_disk,
            'io' => $io_value,
            'cpu' => (int)$this->resource('cpu'),
            'database_limit' => (int)$this->resource('database'),
            'allocation_limit' => (int)$this->resource('allocation'),
            'backup_megabytes_limit' => (int)$this->resource('backups') * $mult_backups,
        ]);

        if (!in_array($this->response_code, [200, 204])) return false;

        // AUTO-FETCH: Get full egg data
        $egg = $this->getEggWithIncludes($this->resource('nest'), $this->resource('egg'));
        if (!$egg) return false;

        $nodeAndAllocations = $this->getNodeAndAllocations();
        if (!$nodeAndAllocations) return false;

        // AUTO-FETCH: Docker image
        $configured_docker_image = $this->resource('docker_image');
        $egg_docker_image = '';
        if (isset($egg['docker_image']) && !empty($egg['docker_image'])) {
            $egg_docker_image = $egg['docker_image'];
        } elseif (isset($egg['docker_images']) && is_array($egg['docker_images']) && !empty($egg['docker_images'])) {
            $egg_docker_image = reset($egg['docker_images']);
        }
        $docker_image = !empty($configured_docker_image) ? $configured_docker_image : $egg_docker_image;

        // AUTO-FETCH: Startup
        $configured_startup = $this->resource('startup_script');
        $egg_startup = $egg['startup'] ?? '';
        $startup = !empty($configured_startup) ? $configured_startup : $egg_startup;

        $data = [
            'egg_id' => $this->resource('egg'),
            'startup' => $startup,
            'docker_image' => $docker_image,
            'skip_scripts' => false
        ];
        
        // AUTO-FETCH: Variables
        $configured_variables = $this->resource('egg_variable');
        if (!empty($configured_variables)) {
            $variables = $configured_variables;
        } else {
            $variables = $this->buildEggVariablesFromEgg($egg);
        }
        
        if (!empty($variables)) {
            $data = $this->parseVariables($variables, $nodeAndAllocations, $data);
        }

        $this->api('servers/' . $this->account_details['extra_details']['device_id'] . '/startup', 'PUT', $data);
        return in_array($this->response_code, [200, 204]);
    }

    public function getPrimaryAllocation($allocations) {
        foreach ($allocations as $allocation) {
            if ($allocation['attributes']['primary']) {
                return $allocation['attributes']['id'];
            }
        }
        return false;
    }

    public function changeFormsFields($account_config) {
        if (empty($account_config)) return true;
        $this->setAccountConfig(array_merge($this->account_config, $account_config));
        return $this->ChangePackage();
    }

    public function getPanelLoginUrl() {
        return $this->_parseHostname() . '/login';
    }

    public function getSynchInfo() {
        $info = $this->getServerDetails();
        $this->details['domain']['value'] = $info['name'];
        $this->options['Memory']['value'] = $info['limits']['memory'];
        return ['suspended' => $info['suspended']];
    }

    public function getProductServers($product_id) {
        if (empty($product_id)) return false;
        $query = $this->db->prepare("SELECT `server` FROM hb_products_modules WHERE `product_id` = :product_id");
        $query->execute(['product_id' => $product_id]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        $query->closeCursor();
        return $result ? explode(',', $result['server']) : false;
    }

    public function getAccounts() {
        $return = [];
        try {
            $servers = $this->api('servers/?include=user');
            foreach ($servers['data'] as $server) {
                $server = $server['attributes'];
                $user = $server['relationships']['user']['attributes'];
                $return[] = [
                    'email' => $user['email'],
                    'username' => $user['username'],
                    'domain' => $server['name'],
                    'status' => $server['suspended'],
                    'extra_details' => [
                        'device_id' => $server['id'],
                        'domain' => $server['name']
                    ]
                ];
            }
        } catch (Exception $e) {
            $this->logger()->error('Wisp error', ['message' => $e->getMessage()]);
        }
        return $return;
    }

    public function getImportType() {
        return ImportAccounts_Model::TYPE_IMPORT_NO_PRODUCTS;
    }

    function parseVariables($variables, $nodeAndAllocations, $data) {
        $nextAllocation = 0;
        $env = explode(';', $variables);
        foreach ($env as $ev) {
            $e = explode(':', $ev);
            if (isset($e[1])) {
                $val = trim($e[1]);
                preg_match_all("/\\\$([a-zA-Z_{}]*)/", $val, $match);
                foreach ($match[1] as $item) {
                    if ($item === '{allocation}') {
                        if (isset($nodeAndAllocations["secondary_allocation_ids"][$nextAllocation])) {
                            $val = (string)$nodeAndAllocations["secondary_allocation_ids"][$nextAllocation][1];
                            $nextAllocation++;
                        }
                    } else if ($item === '{port}') {
                        $val = (string)$nodeAndAllocations["primary_allocation_id"][1];
                    } else {
                        if (isset($this->account_config[$item])) {
                            $val = $this->account_config[$item]["variable_id"] ?? $this->account_config[$item]["value"] ?? $val;
                        }
                    }
                }
                $data['environment'][trim($e[0])] = $val;
            }
        }
        return $data;
    }
}
