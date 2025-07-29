module.exports = {
    darkMode: 'class',
    content: [
        './app/*.php',
        './app/extensions/**/*.php',
        './app/Http/Controllers/*.php',
        './resources/**/*.{blade.php,js,vue}',
        './modules/**/*.{blade.php,js,vue}',
        './modules/**/*.php'
    ],
    theme: {
        extend: {
            colors: {
                'sidebar-light': '#2d353c',
                'sidebar-darker': '#1a2229',
                'lime-nmsprime': '#98d145',
                'whitesmoke': '#f5f5f5',
                'gainsboro': '#dcdcdc',
            },
            screens: {
                'wide': '1921px',
            }
        }
    },
    plugins: [
        require('tailwind-scrollbar')({ nocompatible: true }),
    ],
    corePlugins: {
        visibility: false
    },
    safelist: [
        'bg-whitesmoke',
        'bg-gainsboro',
        // Safelist for wire-elements/modal starts
        'sm:max-w-sm',
        'sm:max-w-md',
        'sm:max-w-md md:max-w-lg',
        'sm:max-w-md md:max-w-xl',
        'sm:max-w-md md:max-w-xl lg:max-w-2xl',
        'sm:max-w-md md:max-w-xl lg:max-w-3xl',
        'sm:max-w-md md:max-w-xl lg:max-w-3xl xl:max-w-4xl',
        'sm:max-w-md md:max-w-xl lg:max-w-3xl xl:max-w-5xl',
        'sm:max-w-md md:max-w-xl lg:max-w-3xl xl:max-w-5xl 2xl:max-w-6xl',
        'sm:max-w-md md:max-w-xl lg:max-w-3xl xl:max-w-5xl 2xl:max-w-7xl',
        // Safelist for wire-elements/modal ends
    ],
}
