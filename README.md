# Contributing to Wisp.gg Module for HostBill

First off, thank you for considering contributing to this project! 🎉

## Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code. Please report unacceptable behavior to the project maintainers.

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the existing issues to avoid duplicates. When you create a bug report, include as many details as possible:

**Required Information:**
- HostBill version
- Wisp.gg panel version
- PHP version
- Module version
- Error messages (exact text)
- Steps to reproduce
- Expected behavior
- Actual behavior

**Optional but Helpful:**
- Debug logs (`/tmp/wisp_debug.log`)
- Screenshots
- Server configuration (resources, egg settings)

**Template:**
```markdown
**Environment:**
- HostBill: 2024.01.00
- Wisp.gg: Latest
- PHP: 8.1
- Module: 1.0.2

**Description:**
[Clear description of the bug]

**Steps to Reproduce:**
1. Go to '...'
2. Click on '...'
3. Scroll down to '...'
4. See error

**Expected Behavior:**
[What you expected to happen]

**Actual Behavior:**
[What actually happened]

**Debug Logs:**
```
[Paste relevant debug log lines]
```

**Screenshots:**
[If applicable]
```

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion, include:

- **Clear title and description**
- **Use case**: Why is this enhancement useful?
- **Expected behavior**: How should it work?
- **Benefits**: Who will benefit and how?
- **Examples**: Code examples or mockups if applicable

**Template:**
```markdown
**Feature Request:** [Short title]

**Use Case:**
[Describe the problem you're trying to solve]

**Proposed Solution:**
[Describe how you envision this feature working]

**Benefits:**
- Benefit 1
- Benefit 2

**Additional Context:**
[Any other context, screenshots, or examples]
```

### Pull Requests

1. **Fork the repository**
   ```bash
   git clone https://github.com/yourusername/hostbill-wispgg-enhanced.git
   cd hostbill-wispgg-enhanced
   ```

2. **Create a feature branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

3. **Make your changes**
   - Follow the [coding standards](#coding-standards)
   - Add comments for complex logic
   - Update documentation if needed

4. **Test your changes**
   - Test with a live HostBill installation
   - Verify with multiple egg types
   - Check debug logs for errors
   - Test both AUTO-FETCH and manual configurations

5. **Commit your changes**
   ```bash
   git add .
   git commit -m "Add: Your feature description"
   ```
   
   Follow [conventional commits](https://www.conventionalcommits.org/):
   - `feat:` New feature
   - `fix:` Bug fix
   - `docs:` Documentation changes
   - `refactor:` Code refactoring
   - `test:` Adding tests
   - `chore:` Maintenance tasks

6. **Push to your fork**
   ```bash
   git push origin feature/your-feature-name
   ```

7. **Open a Pull Request**
   - Provide a clear title and description
   - Reference any related issues
   - Include testing steps
   - Add screenshots if UI changes

## Coding Standards

### PHP Style Guide

Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards:

**Good:**
```php
public function createServer($data) {
    $this->debugLog("Creating server with data: " . json_encode($data));
    
    if (empty($data['nest_id'])) {
        $this->addError('Nest ID is required');
        return false;
    }
    
    return $this->api('servers', 'POST', $data);
}
```

**Bad:**
```php
public function createServer($data){
  if(empty($data['nest_id']))return false;
  return $this->api('servers','POST',$data);
}
```

### Naming Conventions

- **Classes**: PascalCase (`class WispggModule`)
- **Methods**: camelCase (`public function getEggData()`)
- **Variables**: snake_case (`$nest_id`, `$allocation_count`)
- **Constants**: UPPER_SNAKE_CASE (`WISP_DEBUG_ENABLED`)
- **Private methods**: Prefix with underscore (`private function _parseHostname()`)

### Documentation

**Method documentation:**
```php
/**
 * Get egg data with variables included
 * 
 * @param int $nest_id The nest ID
 * @param int $egg_id The egg ID
 * @return array|false Returns egg attributes or false on failure
 */
public function getEggWithIncludes($nest_id, $egg_id) {
    // Implementation
}
```

**Inline comments for complex logic:**
```php
// Try with port filters first, then fallback to all allocations
if (!empty($port_range)) {
    $selected = $this->findAllocationsWithPagination(
        $node_id,
        $allocation_count,
        $node_ip,
        $port_prefix,
        $allowed_ports
    );
    
    // Fallback to no filters if nothing found
    if (!$selected) {
        $this->debugLog("No allocations with filters, trying fallback...");
        $selected = $this->findAllocationsWithPagination(/* ... */);
    }
}
```

### Debug Logging

Always add debug logging for:
- API calls
- Important decisions (AUTO-FETCH vs manual)
- Error conditions
- Allocation searches
- Variable parsing

**Use prefixes:**
```php
$this->debugLog("[AUTO-FETCH] Fetching egg data...");
$this->debugLog("[MANUAL] Using configured docker image");
$this->debugLog("ERROR: Failed to find allocations");
$this->debugLog("Page {$page}: Selected {$count} allocations");
```

### Error Handling

Provide context with errors:

**Good:**
```php
if (!$egg) {
    $this->debugLog("ERROR: Cannot retrieve egg data");
    $this->addError('Cannot retrieve egg data from Wisp.gg');
    $this->addError('Nest ID: ' . $nest_id);
    $this->addError('Egg ID: ' . $egg_id);
    return false;
}
```

**Bad:**
```php
if (!$egg) return false;
```

## Testing Guidelines

### Manual Testing Checklist

Before submitting a PR, test:

- [ ] Server creation with AUTO-FETCH (empty fields)
- [ ] Server creation with manual values (filled fields)
- [ ] Server creation with port range filtering
- [ ] Server creation without port range filtering
- [ ] Package changes (upgrade/downgrade)
- [ ] Server suspension/unsuspension
- [ ] Server termination
- [ ] Multiple egg types (Minecraft, ARK, etc.)
- [ ] Debug logs are generated correctly
- [ ] No PHP errors in HostBill logs

### Test Scenarios

**Scenario 1: AUTO-FETCH**
1. Create product with Nest + Egg selected
2. Leave Egg Variables, Docker Image, Startup Script empty
3. Create server
4. Verify server created with correct variables
5. Check debug log for `[AUTO-FETCH]` entries

**Scenario 2: Manual Override**
1. Create product with Nest + Egg selected
2. Fill in custom Egg Variables, Docker Image, Startup Script
3. Create server
4. Verify server uses custom values
5. Check debug log for `[MANUAL]` entries

**Scenario 3: Port Filtering**
1. Set Port Range to `30100-30200`
2. Create server
3. Verify allocation is within range
4. Check debug log for port filtering

**Scenario 4: Fallback**
1. Set Port Range that doesn't exist
2. Create server
3. Verify fallback to any available port
4. Check debug log for fallback message

## Documentation Updates

When adding features, update:

- [ ] **README.md** - Main documentation
- [ ] **Inline comments** - Code documentation
- [ ] **Method docblocks** - PHPDoc comments
- [ ] **Debug messages** - Helpful logging
- [ ] **CHANGELOG.md** - Version history

## Commit Message Guidelines

Use clear, descriptive commit messages:

**Good:**
```
feat: Add support for dynamic node selection

- Added getNodeByLocation() method
- Modified getNodeAndAllocations() to accept location parameter
- Updated documentation with node selection examples
```

**Bad:**
```
fixed stuff
update
changes
```

## Release Process

1. Update version in `class.wispgg.php`
2. Update `CHANGELOG.md` with changes
3. Update `README.md` if needed
4. Create Git tag: `git tag -a v1.0.3 -m "Version 1.0.3"`
5. Push tag: `git push origin v1.0.3`
6. Create GitHub release with changelog

## Questions?

- **GitHub Issues**: For bugs and features
- **GitHub Discussions**: For questions and ideas
- **Email**: For security issues or private matters

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

---

Thank you for contributing! 🚀
