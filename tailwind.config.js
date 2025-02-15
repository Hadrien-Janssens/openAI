const animate = require("tailwindcss-animate");
const typography = require("@tailwindcss/typography");

/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: ["class"],
    safelist: ["dark"],
    prefix: "",

    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.{js,jsx,vue}",
    ],

    theme: {
        container: {
            center: true,
            padding: "2rem",
            screens: {
                "2xl": "1400px",
            },
        },
        extend: {
            typography: ({ theme }) => ({
                pink: {
                    css: {
                        "--tw-prose-body": "var(--color-pink-800)",
                        "--tw-prose-headings": "var(--color-pink-900)",
                        "--tw-prose-lead": "var(--color-pink-700)",
                        "--tw-prose-links": "var(--color-pink-900)",
                        "--tw-prose-bold": "var(--color-pink-900)",
                        "--tw-prose-counters": "var(--color-pink-600)",
                        "--tw-prose-bullets": "var(--color-pink-400)",
                        "--tw-prose-hr": "var(--color-pink-300)",
                        "--tw-prose-quotes": "var(--color-pink-900)",
                        "--tw-prose-quote-borders": "var(--color-pink-300)",
                        "--tw-prose-captions": "var(--color-pink-700)",
                        "--tw-prose-code": "var(--color-pink-900)",
                        "--tw-prose-pre-code": "var(--color-pink-100)",
                        "--tw-prose-pre-bg": "var(--color-pink-900)",
                        "--tw-prose-th-borders": "var(--color-pink-300)",
                        "--tw-prose-td-borders": "var(--color-pink-200)",
                        "--tw-prose-invert-body": "var(--color-pink-200)",
                        "--tw-prose-invert-headings": "var(--color-white)",
                        "--tw-prose-invert-lead": "var(--color-pink-300)",
                        "--tw-prose-invert-links": "var(--color-white)",
                        "--tw-prose-invert-bold": "var(--color-white)",
                        "--tw-prose-invert-counters": "var(--color-pink-400)",
                        "--tw-prose-invert-bullets": "var(--color-pink-600)",
                        "--tw-prose-invert-hr": "var(--color-pink-700)",
                        "--tw-prose-invert-quotes": "var(--color-pink-100)",
                        "--tw-prose-invert-quote-borders":
                            "var(--color-pink-700)",
                        "--tw-prose-invert-captions": "var(--color-pink-400)",
                        "--tw-prose-invert-code": "var(--color-white)",
                        "--tw-prose-invert-pre-code": "var(--color-pink-300)",
                        "--tw-prose-invert-pre-bg": "rgb(0 0 0 / 50%)",
                        "--tw-prose-invert-th-borders": "var(--color-pink-600)",
                        "--tw-prose-invert-td-borders": "var(--color-pink-700)",
                    },
                },
            }),
            colors: {
                border: "hsl(var(--border))",
                input: "hsl(var(--input))",
                ring: "hsl(var(--ring))",
                background: "hsl(var(--background))",
                foreground: "hsl(var(--foreground))",
                primary: {
                    DEFAULT: "hsl(var(--primary))",
                    foreground: "hsl(var(--primary-foreground))",
                },
                secondary: {
                    DEFAULT: "hsl(var(--secondary))",
                    foreground: "hsl(var(--secondary-foreground))",
                },
                destructive: {
                    DEFAULT: "hsl(var(--destructive))",
                    foreground: "hsl(var(--destructive-foreground))",
                },
                muted: {
                    DEFAULT: "hsl(var(--muted))",
                    foreground: "hsl(var(--muted-foreground))",
                },
                accent: {
                    DEFAULT: "hsl(var(--accent))",
                    foreground: "hsl(var(--accent-foreground))",
                },
                popover: {
                    DEFAULT: "hsl(var(--popover))",
                    foreground: "hsl(var(--popover-foreground))",
                },
                card: {
                    DEFAULT: "hsl(var(--card))",
                    foreground: "hsl(var(--card-foreground))",
                },
            },
            borderRadius: {
                xl: "calc(var(--radius) + 4px)",
                lg: "var(--radius)",
                md: "calc(var(--radius) - 2px)",
                sm: "calc(var(--radius) - 4px)",
            },
            keyframes: {
                "accordion-down": {
                    from: { height: 0 },
                    to: { height: "var(--radix-accordion-content-height)" },
                },
                "accordion-up": {
                    from: { height: "var(--radix-accordion-content-height)" },
                    to: { height: 0 },
                },
                "collapsible-down": {
                    from: { height: 0 },
                    to: { height: "var(--radix-collapsible-content-height)" },
                },
                "collapsible-up": {
                    from: { height: "var(--radix-collapsible-content-height)" },
                    to: { height: 0 },
                },
            },
            animation: {
                "accordion-down": "accordion-down 0.2s ease-out",
                "accordion-up": "accordion-up 0.2s ease-out",
                "collapsible-down": "collapsible-down 0.2s ease-in-out",
                "collapsible-up": "collapsible-up 0.2s ease-in-out",
            },
        },
    },
    plugins: [animate, typography],
};
