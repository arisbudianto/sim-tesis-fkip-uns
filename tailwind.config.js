/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', 'Inter', 'system-ui', 'sans-serif'],
            },
            colors: {
                // Design tokens diambil dari acuan visual SIM-TESIS Portal Redesign.
                primary: {
                    900: '#002B49', // teks judul utama, hero gradient awal
                    800: '#0B3C5D', // brand utama (border, tombol primary, ikon)
                    700: '#0F4A70',
                    600: '#12719E', // hero gradient tengah
                    400: '#1A8FC0', // hero gradient akhir
                },
                accent: {
                    DEFAULT: '#F2B41B', // kuning CTA & aksen highlight
                    light: '#FFD35C',
                    soft: '#FDF4DD',
                },
            },
            borderRadius: {
                xl2: '1rem',
            },
        },
    },
    plugins: [],
};
