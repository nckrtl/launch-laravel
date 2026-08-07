import type { PageProps } from "@inertiajs/core";
import type { LucideIcon } from "lucide-react";

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface SharedData extends PageProps {
    name: string;
    auth: Auth;
    location: {
        current: string;
        previous: string;
    };
    navigation: Navigation;
    sidebarOpen: boolean;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    language: string;
}

export type BreadcrumbItemType = BreadcrumbItem;
export type {
    AppLayoutProps,
    AuthLayoutProps,
    Passkey,
    TwoFactorSecretKey,
    TwoFactorSetupData,
} from "@/lib/types";
