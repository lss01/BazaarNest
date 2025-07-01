# BazaarNest

This template should help get you started developing with Vue 3 in Vite.

## Recommended IDE Setup

[VSCode](https://code.visualstudio.com/) + [Volar](https://marketplace.visualstudio.com/items?itemName=Vue.volar) (and disable Vetur).

## Customize configuration

See [Vite Configuration Reference](https://vite.dev/config/).

## Project Setup
```sh
cd C:\xampp\htdocs\BazaarNest
```

```sh
npm install
```

### Compile and Hot-Reload for Development

```sh
npm run dev
```

### Compile and Minify for Production

```sh
npm run build
```

### Connecting with Localhost php for backend
```sh
cd C:\xampp\htdocs\BazaarNest\backend\public
```
```sh
php -S localhost:8080
```
### If port conflict to connect with Localhost php for backend
api.js(line 7) & vite.config.js(line 21) port change based on your own port(if 8080 blocked, change to 8000)
```sh
php -S localhost:8000
```
