# Frontend Tooling

- This project formats, lints and checks JavaScript, TypeScript and Vue with Vite+ (`vp`). Prettier and ESLint are not installed, so don't call them.
- Run `pnpm run check` to check formatting and lint rules, `pnpm run check:fix` to fix them, and `pnpm run types:check` for type errors.
- To check only the files you changed, pass them to `pnpm exec vp check <files>`.
