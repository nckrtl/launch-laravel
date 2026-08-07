import "../css/app.css";

import { createInertiaApp } from "@inertiajs/react";
import AppLayout from "@/components/app-layout";
import AuthLayout from "@/components/auth-layout";
import SettingsLayout from "@/components/settings-layout";
import { initializeTheme } from "@/hooks/use-appearance";

function allowClicksBehindLaravelToolbar(): void {
    const host = document.getElementById("laravel-toolbar-shadow-host");
    const root = host?.shadowRoot;
    const toolbar = root?.getElementById("toolbar");

    if (!(host instanceof HTMLElement) || !(toolbar instanceof HTMLElement)) {
        return;
    }

    host.style.pointerEvents = "none";
    toolbar.style.pointerEvents = "none";

    for (const section of Array.from(toolbar.children)) {
        if (!(section instanceof HTMLElement)) {
            continue;
        }

        section.style.pointerEvents = "none";

        for (const group of Array.from(section.children)) {
            if (group instanceof HTMLElement) {
                group.style.pointerEvents = "auto";
            }
        }
    }
}

function watchLaravelToolbar(): void {
    allowClicksBehindLaravelToolbar();

    window.addEventListener("laravel-toolbar:html-updated", () => {
        requestAnimationFrame(allowClicksBehindLaravelToolbar);
    });

    const observer = new MutationObserver(() => allowClicksBehindLaravelToolbar());
    observer.observe(document.documentElement, { childList: true, subtree: true });
}

createInertiaApp({
    title: (title) =>
        title
            ? `${title} - ${import.meta.env.VITE_APP_NAME || "Laravel"}`
            : import.meta.env.VITE_APP_NAME || "Laravel",
    layout: (_name) => {
        switch (true) {
            case _name.startsWith("auth/"):
                return AuthLayout;
            case _name.startsWith("settings/"):
                return [AppLayout, SettingsLayout];
            case _name === "Dashboard":
                return AppLayout;
            default:
                return null;
        }
    },
});

initializeTheme();
watchLaravelToolbar();
