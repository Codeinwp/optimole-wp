> [!IMPORTANT]  
> Tests run on the testing.optimole.com WordPress instance, so running in a local environment might not reflect the actual results.

### To run e2e tests locally:

In the root directory of the project, run:
```bash
npm install --frozen-lockfile
npx playwright install
npm run e2e:open
```
