import { KwdAuthLayout } from '@/Layouts/modern';

interface Props {
    children: React.ReactNode;
    title?: string;
    description?: string;
    showBackButton?: boolean;
    backUrl?: string;
}

export default function AuthLayout({
    children,
    title = 'Yamsoo',
    description,
    showBackButton = false,
    backUrl = '/',
    ...props
}: Props) {
    return (
        <KwdAuthLayout
            title={title}
            showBackButton={showBackButton}
            backUrl={backUrl}
            {...props}
        >
            {children}
        </KwdAuthLayout>
    );
}
