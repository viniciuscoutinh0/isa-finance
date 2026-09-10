import { defineConfig } from "vite";
import laravel, { refreshPaths } from "laravel-vite-plugin";
import { bunny } from "laravel-vite-plugin/fonts";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: [...refreshPaths, "app/Livewire/**"],
            fonts: [
                bunny("Inter", {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        host: true,
        hmr: {
            host: "localhost",
        },
        watch: {
            ignored: ["**/storage/framework/views/**"],
        },
    },
});
