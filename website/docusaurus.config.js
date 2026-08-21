// @ts-check
const { themes: prismThemes } = require('prism-react-renderer')

/** @type {import('@docusaurus/types').Config} */
const config = {
  title: 'Stry',
  tagline: 'Self-hosted video-on-demand streaming platform, built on Laravel',

  url: 'https://francoism90.github.io',
  baseUrl: '/stry/',

  organizationName: 'francoism90',
  projectName: 'stry',
  deploymentBranch: 'gh-pages',
  trailingSlash: false,

  onBrokenLinks: 'throw',
  onBrokenMarkdownLinks: 'warn',

  i18n: {
    defaultLocale: 'en',
    locales: ['en'],
  },

  presets: [
    [
      'classic',
      /** @type {import('@docusaurus/preset-classic').Options} */
      ({
        docs: {
          path: '../docs',
          routeBasePath: '/',
          sidebarPath: require.resolve('./sidebars.js'),
          editUrl: 'https://github.com/francoism90/stry/edit/main/docs/',
        },
        blog: false,
        theme: {
          customCss: require.resolve('./src/css/custom.css'),
        },
      }),
    ],
  ],

  themes: [
    [
      '@easyops-cn/docusaurus-search-local',
      /** @type {import('@easyops-cn/docusaurus-search-local').PluginOptions} */
      ({
        hashed: true,
        indexBlog: false,
        docsRouteBasePath: '/',
      }),
    ],
  ],

  themeConfig:
    /** @type {import('@docusaurus/preset-classic').ThemeConfig} */
    ({
      navbar: {
        title: 'Stry',
        items: [
          {
            href: 'https://github.com/francoism90/stry',
            label: 'GitHub',
            position: 'right',
          },
        ],
      },
      footer: {
        style: 'dark',
        links: [
          {
            title: 'Docs',
            items: [
              { label: 'Documentation', to: '/' },
              { label: 'Production Setup', to: '/production' },
              { label: 'Development', to: '/development' },
            ],
          },
          {
            title: 'More',
            items: [
              { label: 'GitHub', href: 'https://github.com/francoism90/stry' },
              { label: 'laravel-podman', href: 'https://github.com/foxws/laravel-podman' },
              { label: 'lpod CLI', href: 'https://github.com/foxws/lpod' },
            ],
          },
        ],
        copyright: `Copyright © ${new Date().getFullYear()} francoism90. Built with Docusaurus.`,
      },
      prism: {
        theme: prismThemes.github,
        darkTheme: prismThemes.dracula,
      },
    }),
}

module.exports = config
