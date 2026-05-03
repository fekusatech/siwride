import type { Auth } from '@/types/auth';

// Extend ImportMeta interface for Vite...
interface Window {
    grecaptcha?: {
        render: (
            element: string | HTMLElement,
            parameters: Record<string, unknown>,
        ) => number;
        getResponse: (widgetId?: number) => string;
        reset: (widgetId?: number) => void;
    };
    handleAdminRecaptchaSuccess?: (token: string) => void;
    handleAdminRecaptchaExpired?: () => void;
    handleCustomerRecaptchaSuccess?: (token: string) => void;
    handleCustomerRecaptchaExpired?: () => void;
}

declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(
            pattern: string,
            options?: { eager?: boolean },
        ) => Record<string, T>;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            sidebarOpen: boolean;
            settings: {
                business_name: string;
                logo: string | null;
                favicon: string | null;
                recaptcha_enabled: string;
                recaptcha_site_key: string | null;
                recaptcha_secret_key: string | null;
                updated_at: string | null;
            };
            [key: string]: unknown;
        };
    }
}
