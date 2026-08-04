export interface User {
    id: number;
    name: string;
    email: string;
    whatsapp_number: string;
    email_verified_at?: string;
    whatsapp_verified_at?: string;
    must_change_password: boolean;
    avatar_path?: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User | null;
        roles: string[];
        permissions: string[];
        unreadNotificationsCount: number;
    };
    flash: {
        success: string | null;
        error: string | null;
        status: string | null;
    };
    settings: {
        whatsapp: string;
        email: string;
        phone: string | null;
        address: string;
        mapEmbedUrl: string | null;
        instagram: string | null;
        facebook: string | null;
        tiktok: string | null;
    };
};
