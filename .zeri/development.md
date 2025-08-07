# simple-token-auth - Development Practices

## Code Standards & Quality

### Code Style
Follow PSR-12 standards

**⚠️ MANDATORY: Run `./vendor/bin/pint` after every PHP file modification**

### Naming Conventions
CamelCase for classes, snake_case for variables

### File Organization
Organize by feature/domain

### Documentation Standards
- Use PHPDoc for all public methods.
- Provide clear comments in the configuration file.
- Add inline comments for security-related code.
- Include usage examples in docblocks.
- The `README.md` should contain:
    1. Installation instructions
    2. Basic usage examples
    3. Advanced configuration
    4. Security best practices
    5. Command reference
    6. Troubleshooting guide

### Security Guidelines
- Use `random_bytes()` for token generation.
- Use `hash_equals()` for constant-time comparison to prevent timing attacks.
- Implement cache-based rate limiting with SHA-256 hashed keys.
- Ensure comprehensive audit logging with IP tracking.
- Do not expose tokens in logs or error messages; use token masking.
- Sanitize all inputs and use prepared statements where applicable.

### Performance Considerations
- Optimize database queries, cache where appropriate.
- Aim for sub-10ms authentication overhead.

---

## Architecture Decisions

### Decision Template
- **Date**:
- **Decision**:
- **Context**:
- **Options Considered**:
- **Chosen Option**:
- **Rationale**:
- **Consequences**:

### Recent Decisions
Initial technology stack selection

### Key Architecture Decisions
Framework choice, database selection, deployment strategy

### Technology Choices
PHP, Laravel - chosen for team expertise and project requirements

### Design Patterns
MVC pattern, Repository pattern for data access

---

## Code Patterns

### Standard Patterns
MVC, Repository, Service Layer patterns

### Component Patterns
Reusable components, consistent API structure

### Data Handling Patterns
Eloquent models, validation, serialization

### Error Handling Patterns
- Use standardized JSON error responses.
- Use appropriate HTTP status codes (e.g., 401 for auth failures).
- Log errors with details but without exposing tokens.
- Implement graceful fallbacks for missing configurations.
- Use custom exceptions for specific error conditions.

### Testing Patterns
Arrange-Act-Assert, test factories, mocking external services

### Configuration Patterns
Environment-based config, feature flags

### Examples

#### Middleware Usage
```php
// Basic usage
Route::middleware('simple-token-auth')

// Service-specific
Route::middleware('simple-token-auth:service_name')
```

#### Command Interface
```bash
php artisan simple-token:generate [service] [--length=64] [--env]
php artisan simple-token:info
```

---

## Development Workflows

### Development Process
Feature branch workflow with code review

### Before Starting Development
Check latest main branch, create feature branch

### Implementation Steps
1. Write tests 2. Implement feature 3. Run tests 4. Code review

### Testing Workflow
The testing strategy covers unit, integration, and security tests.

#### Testing Requirements
- **Unit Tests**:
    - Token validation logic
    - Hash comparison safety
    - Rate limiting functionality
    - Token generation randomness
    - Configuration parsing
- **Integration Tests**:
    - Middleware flow
    - Multiple token scenarios
    - Service-specific authentication
    - Command execution
    - Laravel service integration
- **Security Tests**:
    - Timing attack resistance
    - Rate limiting effectiveness
    - Token brute force scenarios
    - Log data sanitization

### Code Review Process
Pull request review with at least one approval

#### Code Review Guidelines
All code must be reviewed before merge

### Deployment Steps
- Manage dependencies and publish the package via Composer.
- Use Laravel's package auto-discovery.
- Follow semantic versioning.
- Provide clear instructions for `.env` variable naming.
- Make configuration publishable.
- Register the service provider and middleware alias.

### Troubleshooting Common Issues
Check logs, reproduce issue, write failing test, fix, verify

---

## Feature Planning

### Planning Process
Requirements gathering, technical design, estimation

### Requirements Gathering
Stakeholder interviews, user stories, acceptance criteria

### Technical Analysis
Architecture review, dependency analysis, risk assessment

### Design Considerations
User experience, performance, security, maintainability

### Implementation Planning
Break down into tasks, estimate effort, plan sprints

### Risk Assessment
Identify technical risks, mitigation strategies

### Timeline Estimation
Story points, velocity tracking, buffer for unknowns

---

## Debugging & Maintenance

### Debugging Process
Reproduce, isolate, identify root cause, fix, verify

### Common Issues
Database connection, configuration errors, dependency issues

### Debugging Tools
Debugger, logging, profiler, monitoring tools

### Log Analysis
Check application logs, error logs, system logs

### Performance Debugging
Profiling, query analysis, resource monitoring

### Error Tracking
Use error tracking service, categorize errors, prioritize fixes

### Resolution Documentation
Document solution, update runbooks, share learnings

---

## Specification Implementation

### Creating Specifications

Use `zeri add-spec <name>` to create new feature specifications:

```bash
# Create a new specification
zeri add-spec "feature-name"

# This creates .zeri/specs/feature-name.md with the standard template
```

**Specification Structure:**
- **Overview**: Brief description of the feature or enhancement
- **Requirements**: Detailed list of functional requirements
- **Implementation Notes**: Technical considerations and dependencies
- **TODO**: Checklist for tracking implementation progress

### Specification Workflow

1. **Create Specification**: Use `zeri add-spec` command to create structured requirements
2. **Plan Implementation**: Break down requirements into actionable tasks
3. **Implement Features**: Follow the TODO checklist step by step
4. **Mark Progress**: Update TODOs in real-time during development
5. **Review and Complete**: Ensure all requirements are met

### Best Practices

**Specification Content:**
- Write clear, actionable requirements
- Include technical considerations and dependencies
- Reference existing patterns and conventions
- Consider testing and documentation needs

**Implementation Process:**
- Always start with a specification for non-trivial features
- Break complex features into smaller, manageable tasks
- Follow established coding patterns and conventions
- Write tests alongside implementation

### TODO Marking

Mark TODO items as complete when implementing specifications:

- Mark checkboxes as `- [x]` when completing each implementation step
- This helps track progress and manage development workflow
- Update TODOs in real-time during implementation

**Example:**
```markdown
## TODO
- [x] Design and plan implementation
- [x] Implement core functionality
- [ ] Add tests
- [ ] Update documentation
- [ ] Review and refine
- [ ] Mark specification as complete
```

### Specification Directory Structure

```
.zeri/
├── specs/                    # Feature specifications
│   ├── feature-name.md      # Individual specification files
│   └── another-feature.md   # Each spec is self-contained
└── templates/
    └── spec.md              # Template for new specifications
```
