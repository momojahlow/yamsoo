import { Head, useForm, Link } from '@inertiajs/react';
import { LoaderCircle, Eye, EyeOff } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type LoginForm = {
    email: string;
    password: string;
    remember: boolean;
};

interface LoginProps {
    status?: string;
    canResetPassword: boolean;
}

export default function Login({ status, canResetPassword }: LoginProps) {
    const { data, setData, post, processing, errors, reset } = useForm<Required<LoginForm>>({
        email: '',
        password: '',
        remember: false,
    });

    const [showPassword, setShowPassword] = useState(false);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
            onError: (errors) => {
                // Log des erreurs pour debug
                console.error('Erreurs de connexion:', errors);
            },
        });
    };

    return (
        <div className="min-h-screen bg-gradient-to-br from-orange-50 via-white to-red-50 flex items-center justify-center p-4">
            <Head title="Connexion - Yamsoo" />

            <div className="w-full max-w-md">
                <div className="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
                    {/* Logo et titre */}
                    <div className="text-center mb-8">
                        <Link href={route('home')} className="inline-block">
                            <h1 className="text-4xl font-bold text-transparent bg-gradient-to-r from-orange-500 to-red-500 bg-clip-text mb-2">
                                Yamsoo!
                            </h1>
                        </Link>
                        <h2 className="text-2xl font-semibold text-gray-800 mb-2">Connexion</h2>
                        <p className="text-gray-600">Connectez-vous à votre compte Yamsoo</p>
                    </div>

                    {status && (
                        <div className="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <p className="text-sm text-green-700 text-center">{status}</p>
                        </div>
                    )}

                    {/* Messages d'erreur généraux */}
                    {(errors.email || errors.password) && (
                        <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <div className="flex items-center">
                                <div className="flex-shrink-0">
                                    <svg className="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                                    </svg>
                                </div>
                                <div className="ml-3">
                                    <h3 className="text-sm font-medium text-red-800">
                                        Erreur de connexion
                                    </h3>
                                    <div className="mt-2 text-sm text-red-700">
                                        <p>Veuillez vérifier vos identifiants et réessayer.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    <form className="space-y-6" onSubmit={submit}>
                        {/* Email */}
                        <div>
                            <Label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-2">
                                Email
                            </Label>
                            <Input
                                id="email"
                                type="email"
                                required
                                autoFocus
                                tabIndex={1}
                                autoComplete="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                placeholder="exemple@email.com"
                                className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200"
                            />
                            <InputError message={errors.email} />
                        </div>

                        {/* Mot de passe */}
                        <div>
                            <Label htmlFor="password" className="block text-sm font-medium text-gray-700 mb-2">
                                Mot de passe
                            </Label>
                            <div className="relative">
                                <Input
                                    id="password"
                                    type={showPassword ? "text" : "password"}
                                    required
                                    tabIndex={2}
                                    autoComplete="current-password"
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    placeholder="••••••••"
                                    className="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200"
                                />
                                <button
                                    type="button"
                                    onClick={() => setShowPassword(!showPassword)}
                                    className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 transition-colors"
                                >
                                    {showPassword ? <EyeOff className="w-5 h-5" /> : <Eye className="w-5 h-5" />}
                                </button>
                            </div>
                            <InputError message={errors.password} />
                        </div>

                        {/* Options */}
                        <div className="flex items-center justify-between">
                            <div className="flex items-center space-x-2">
                                <Checkbox
                                    id="remember"
                                    name="remember"
                                    checked={data.remember}
                                    onClick={() => setData('remember', !data.remember)}
                                    tabIndex={3}
                                />
                                <Label htmlFor="remember" className="text-sm text-gray-600">
                                    Se souvenir de moi
                                </Label>
                            </div>

                            {canResetPassword && (
                                <TextLink
                                    href={route('password.request')}
                                    className="text-sm text-orange-600 hover:text-orange-700 font-medium"
                                    tabIndex={5}
                                >
                                    Mot de passe oublié ?
                                </TextLink>
                            )}
                        </div>

                        {/* Bouton de connexion */}
                        <Button
                            type="submit"
                            className="w-full bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 transform hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                            tabIndex={4}
                            disabled={processing}
                        >
                            {processing ? (
                                <div className="flex items-center justify-center">
                                    <LoaderCircle className="h-5 w-5 animate-spin mr-2" />
                                    Connexion...
                                </div>
                            ) : (
                                'Connexion'
                            )}
                        </Button>

                        {/* Lien d'inscription */}
                        <div className="text-center pt-4 border-t border-gray-200">
                            <p className="text-gray-600">
                                Vous n'avez pas de compte ?{' '}
                                <TextLink
                                    href={route('register')}
                                    className="text-orange-600 hover:text-orange-700 font-semibold"
                                    tabIndex={6}
                                >
                                    Créer un compte
                                </TextLink>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
}
