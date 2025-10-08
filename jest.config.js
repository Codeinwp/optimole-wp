module.exports = {
  testEnvironment: 'jsdom',
  testMatch: ['**/__tests__/**/*.test.js'],
  collectCoverageFrom: [
    'assets/js/modules/**/*.js',
    '!assets/js/modules/**/*.test.js',
    '!assets/js/modules/README.md',
    '!assets/js/modules/AGENTS.md'
  ],
  coverageDirectory: 'coverage',
  coverageReporters: ['text', 'lcov', 'html'],
  moduleFileExtensions: ['js'],
  transform: {
    '^.+\\.js$': ['babel-jest', {
      presets: [
        ['@babel/preset-env', {
          targets: {
            node: 'current'
          }
        }]
      ]
    }]
  },
  setupFilesAfterEnv: ['<rootDir>/jest.setup.js'],
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/assets/js/modules/$1'
  }
};

