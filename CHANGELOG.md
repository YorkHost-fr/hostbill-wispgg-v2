# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Planned Features
- Dynamic node selection based on location
- Multi-node load balancing
- Allocation reservation system
- WebSocket support for real-time updates

---

## [1.0.2] - 2025-01-04 - Enhanced Edition by YorkHost

### Added
- **Auto-Fetch Feature**: Automatically retrieve egg variables, startup commands, and Docker images from Wisp.gg API
  - New method `getEggWithIncludes()` to fetch egg data with variables
  - New method `buildEggVariablesFromEgg()` to parse variables automatically
  - Smart logic: Use auto-fetch when fields empty, allow manual override
- **Advanced Pagination Support**: Handle large allocation pools efficiently
  - Configurable page size (default: 50 per page)
  - Safety limit (max: 20 pages)
  - Early termination when enough allocations found
- **Port Range Filtering**: Filter allocations by port ranges
  - Support for ranges: `30100-30200`
  - Support for lists: `25565,25566,25567`
  - Support for single ports: `25565`
  - Automatic prefix extraction for API filtering
- **IP-Port Filtering**: Use Wisp.gg's native `filter[ip_port]` parameter
  - Faster searches using database indexes
  - Reduced bandwidth usage
  - Format: `IP:PORT_PREFIX` (e.g., `83.150.218.137:30`)
- **Enhanced Debug Logging**: Comprehensive logging with categorized prefixes
  - `[AUTO-FETCH]` prefix for auto-fetch operations
  - `[MANUAL]` prefix for manual overrides
  - Detailed pagination logs
  - API request/response logging
- **Fallback Mechanisms**: Graceful degradation when filters don't match
  - Try with port filters first
  - Fallback to all available allocations
  - Clear logging of fallback decisions

### Changed
- **Updated field descriptions** to indicate AUTO-FETCH capability
  - "Egg variables" now shows `[AUTO-FETCH]` prefix
  - "Docker Image" now shows `[AUTO-FETCH]` prefix
  - "Startup script" now shows `[AUTO-FETCH]` prefix
- **Improved error messages** with context and suggestions
  - HTTP error codes include detailed explanations
  - API errors show field names and values
  - Endpoint and method included in error output
- **Enhanced allocation search** with better logging
  - Page-by-page progress tracking
  - Selected/rejected allocation counts
  - Total progress indicators
- **Docker image handling** supports both formats
  - Single string: `docker_image`
  - Object/array: `docker_images`

### Fixed
- **Allocation search** with large port ranges
  - Pagination ensures all allocations are considered
  - Port validation happens client-side for accuracy
- **Docker image** retrieval from eggs
  - Handles both `docker_image` (string) and `docker_images` (object)
  - Safely gets first image from object using `reset()`
- **Error handling** for HTTP codes
  - All HTTP 4xx and 5xx codes properly handled
  - Context preserved for debugging
  - Helpful messages for common errors (401, 403, 404, 422, 429)
- **Variable parsing** when no variables exist
  - No longer fails if egg has no variables
  - Logs warning instead of error
  - Continues with server creation

### Improved
- **API efficiency** with smart filtering
  - Reduced number of API calls
  - Better use of Wisp.gg's filtering capabilities
  - Pagination only when necessary
- **Code documentation** with detailed comments
  - Method docblocks with parameters and return types
  - Inline comments for complex logic
  - Clear section headers in code
- **Debug output** with structured logging
  - Consistent timestamp format
  - JSON pretty-print for complex objects
  - Progress indicators for long operations

---

## [1.0.1] - 2023-XX-XX - Original by Xephia.eu

### Added
- Initial release of HostBill Wisp.gg module
- Basic server provisioning (Create, Suspend, Unsuspend, Terminate)
- User management (create, retrieve by external ID)
- Resource configuration (CPU, RAM, Disk, Swap, Databases, Backups)
- Allocation management (primary + secondary ports)
- Docker image configuration
- Startup script configuration
- Manual egg variable configuration
- Package/plan changes with resource updates
- Server reinstall and rebuild functions
- Multi-language support (English, Czech)
- Basic error handling
- Connection testing

### Features
- HostBill integration via provisioning module
- Wisp.gg API v1 support
- Pterodactyl-compatible API calls
- Server lifecycle management
- Custom startup commands
- Environment variable configuration
- Location and nest/egg selection
- Block IO weight control

---

## Version Comparison

| Feature | v1.0.1 (Original) | v1.0.2 (Enhanced) |
|---------|-------------------|-------------------|
| **Server Creation** | ✅ Manual config | ✅ AUTO-FETCH |
| **Egg Variables** | ✅ Manual input | ✅ Auto-retrieved |
| **Docker Image** | ✅ Manual input | ✅ Auto-retrieved |
| **Startup Command** | ✅ Manual input | ✅ Auto-retrieved |
| **Port Filtering** | ❌ No | ✅ Advanced ranges |
| **Pagination** | ❌ No | ✅ Yes (50/page) |
| **IP-Port Filter** | ❌ No | ✅ Native API filter |
| **Fallback Logic** | ❌ No | ✅ Yes |
| **Debug Logging** | ⚠️ Basic | ✅ Comprehensive |
| **Error Messages** | ⚠️ Generic | ✅ Detailed context |
| **Documentation** | ⚠️ Minimal | ✅ Extensive |

---

## Migration Guide

### From 1.0.1 to 1.0.2

1. **Backup your current installation**
   ```bash
   cp class.wispgg.php class.wispgg.php.backup
   ```

2. **Replace the file**
   ```bash
   cp class.wispgg_modified.php class.wispgg.php
   ```

3. **Update product configurations (optional)**
   - For AUTO-FETCH: Clear "Egg variables", "Docker Image", "Startup script" fields
   - For manual: Keep existing values (they will override AUTO-FETCH)

4. **Enable debug logging (recommended for first use)**
   ```php
   define('WISP_DEBUG_ENABLED', true);
   ```

5. **Test with a development product**
   - Create a test product
   - Leave egg fields empty
   - Create a server
   - Verify AUTO-FETCH in logs
   - Check server in Wisp.gg panel

6. **No database changes required** - The module is fully compatible

### Breaking Changes

**None!** Version 1.0.2 is 100% backward compatible with 1.0.1.
- Manual configurations still work
- Existing servers unaffected
- API calls remain the same
- Database schema unchanged

---

## Known Issues

### v1.0.2
- Static node ID (91) - Dynamic node selection not yet implemented
- No WebSocket support for real-time updates
- No allocation reservation/locking mechanism

### v1.0.1
- No pagination - Could fail with large allocation pools
- Manual variable input required - Prone to errors
- Limited debugging - Hard to troubleshoot issues
- Basic error messages - Lacked context

---

## Credits

### v1.0.2 Enhanced Edition
- **Maintained by**: [YorkHost](https://yorkhost.fr)
- **Contributors**: Community contributors
- **Based on**: xephia-eu/hostbill-wispgg v1.0.1

### v1.0.1 Original
- **Author**: [Xephia.eu](https://github.com/xephia-eu)
- **Repository**: [xephia-eu/hostbill-wispgg](https://github.com/xephia-eu/hostbill-wispgg) (archived)

---

## Links

- **Repository**: https://github.com/yourusername/hostbill-wispgg-enhanced
- **Issues**: https://github.com/yourusername/hostbill-wispgg-enhanced/issues
- **Original**: https://github.com/xephia-eu/hostbill-wispgg
- **HostBill**: https://hostbillapp.com/
- **Wisp.gg**: https://wisp.gg/

---

[Unreleased]: https://github.com/yourusername/hostbill-wispgg-enhanced/compare/v1.0.2...HEAD
[1.0.2]: https://github.com/yourusername/hostbill-wispgg-enhanced/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/xephia-eu/hostbill-wispgg/releases/tag/v1.0.1
