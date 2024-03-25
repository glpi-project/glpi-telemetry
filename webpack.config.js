const Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or subdirectory deploy
    //.setManifestKeyPrefix('build/')

    // Javascript entries
    .addEntry('js/app', './assets/js/app.js')
    .addEntry('js/dashboard', './assets/js/dashboard.js')
    .addEntry('js/reference', './assets/js/reference.js')
    .addEntry('js/cookieconsent-config', './assets/js/cookieconsent-config.js')

    // Style entries
    .addEntry('styles/app', './assets/styles/app.scss')
    .addEntry('styles/reference', './assets/styles/reference.scss')

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    // .enableSingleRuntimeChunk()
    .disableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(true)

    // enables Sass/SCSS support
    .enableSassLoader()

    // embed small images as base64 in complied CSS
    .configureImageRule({
        type: 'asset',
    })

    .copyFiles({
        from: './assets/images',
        to: 'images/[path][name].[hash:8].[ext]',
        pattern: /\.(png|jpg|jpeg)$/
    })

    .cleanupOutputBeforeBuild()
;

module.exports = Encore.getWebpackConfig();
