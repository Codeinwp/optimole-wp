> [!IMPORTANT]  
> CI deploys to and tests a shared QA instance. Local runs default to `http://localhost`; set `E2E_BASE_URL` to your local WordPress URL before running the suite.

### To run e2e tests locally:

In the root directory of the project, run:
```bash
npm ci
npx playwright install
E2E_BASE_URL=http://localhost:8080 npm run e2e:open
```

```bash
#You can run the tests in headless mode by running:
E2E_BASE_URL=http://localhost:8080 npm run e2e:run

# Run an individual test file:
E2E_BASE_URL=http://localhost:8080 npx playwright test tests/e2e/<test-name>.spec.ts
```






