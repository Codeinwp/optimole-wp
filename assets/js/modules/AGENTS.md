# Optimole JavaScript Modules - Agent Instructions


## Code standards
- **CRITICAL**: All functions must have JSDoc comments with `@param` and `@return` annotations
- Use ES6 modules with `import`/`export` syntax
- Follow camelCase naming for functions and variables
- Use `const`/`let` instead of `var`
- Always handle errors gracefully with try/catch blocks
- Use descriptive variable names and avoid abbreviations

## Testing instructions
- Test modules individually by importing them in browser console
- Use `optmlLogger.isDebug()` to enable debug logging for testing
- Verify API calls work with both `sendBeacon` and `fetch` fallback
- Check storage functionality with different URL/device combinations
- Test error handling by simulating network failures
 