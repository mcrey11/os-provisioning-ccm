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
        // Dashboard color classes
        'bg-yellow-50', 'bg-yellow-100', 'bg-yellow-200', 'bg-yellow-600', 'bg-yellow-800', 'bg-yellow-900',
        'text-yellow-400', 'text-yellow-600', 'text-yellow-800',
        'border-yellow-200', 'border-yellow-700',
        'hover:text-yellow-200', 'hover:text-yellow-800',
        'dark:bg-yellow-900', 'dark:bg-yellow-800', 'dark:text-yellow-400', 'dark:text-yellow-200', 'dark:border-yellow-700',
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
