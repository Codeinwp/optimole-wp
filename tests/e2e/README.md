> [!IMPORTANT]  
> Tests run on the testing.optimole.com WordPress instance, so running in a local environment might not reflect the actual results.

### To run e2e tests locally:

In the root directory of the project, run:
```bash
npm install --frozen-lockfile
npx playwright install
npm run e2e:open
```

```bash
#You can run the tests in headless mode by running:
npm run e2e:run

# If you want to run individual tests, you can do so by running:
npm run e2e:test tests/e2e/<test-name>

# You can directly run the tests using playwright by running:
npx playwright test tests/e2e/<test-name>
```








