# bigduck-nuxt

> Nuxt.js project

## Build Setup

``` bash
# install dependencies
$ npm install # Or yarn install

# serve with hot reload at localhost:3000
$ npm run dev

# build for production and launch server
$ npm run build
$ npm start

# generate static project
$ npm run generate
```

For detailed explanation on how things work, checkout the [Nuxt.js docs](https://github.com/nuxt/nuxt.js).

## Wordpress Setup

``` bash
# setup laravel valet for wordpress.bigduck.dev
cd wordpress
valet link wordpress.bigduck

# sync content including,wordpress itself, plugins, uploads, themes and most importantly the database
gulp pull 

# pull just content (plugins, uploads, themes)
gulp content

# sync just database
gulp db

```