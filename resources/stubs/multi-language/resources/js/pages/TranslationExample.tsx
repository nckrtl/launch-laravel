import { Head } from "@inertiajs/react";
import { __, setLocale, useLocale } from "@nckrtl/launch-ui/i18n";

export default function TranslationExample() {
    const locale = useLocale();

    return (
        <main className="mx-auto flex min-h-screen max-w-2xl flex-col justify-center gap-6 px-6">
            <Head title={__("Translations")} />

            <div className="space-y-2">
                <p className="text-sm font-medium text-muted-foreground">
                    {__("Current language: :locale", { locale: locale.toUpperCase() })}
                </p>
                <h1 className="text-4xl font-semibold tracking-tight">
                    {__("Hello :name", { name: "Launch" })}
                </h1>
            </div>

            <div className="flex gap-3">
                {(["en", "nl"] as const).map((language) => (
                    <button
                        className="rounded-md border px-4 py-2 text-sm font-medium disabled:opacity-50"
                        disabled={locale === language}
                        key={language}
                        onClick={() => setLocale(language)}
                        type="button"
                    >
                        {language.toUpperCase()}
                    </button>
                ))}
            </div>
        </main>
    );
}
