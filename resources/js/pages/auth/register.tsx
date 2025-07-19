import { Head, useForm, Link } from '@inertiajs/react';
import { LoaderCircle, Eye, EyeOff } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type RegisterForm = {
    first_name: string;
    last_name: string;
    email: string;
    mobile: string;
    birth_date: string;
    gender: string;
    password: string;
    password_confirmation: string;
};

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm<Required<RegisterForm>>({
        first_name: '',
        last_name: '',
        email: '',
        mobile: '',
        birth_date: '',
        gender: '',
        password: '',
        password_confirmation: '',
    });

    const [showPassword, setShowPassword] = useState(false);
    const [showPasswordConfirmation, setShowPasswordConfirmation] = useState(false);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <div className="min-h-screen bg-gradient-to-br from-orange-50 via-white to-red-50 flex items-center justify-center p-4">
            <Head title="Inscription - Yamsoo" />

            <div className="w-full max-w-2xl">
                <div className="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
                    {/* Logo et titre */}
                    <div className="text-center mb-8">
                        <Link href={route('home')} className="inline-block">
                            <h1 className="text-4xl font-bold text-transparent bg-gradient-to-r from-orange-500 to-red-500 bg-clip-text mb-2">
                                Yamsoo!
                            </h1>
                        </Link>
                        <h2 className="text-2xl font-semibold text-gray-800 mb-2">Créer un compte</h2>
                        <p className="text-gray-600">Rejoignez votre réseau familial</p>
                    </div>

                    <form className="space-y-6" onSubmit={submit}>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {/* Prénom */}
                            <div>
                                <Label htmlFor="first_name" className="block text-sm font-medium text-gray-700 mb-2">
                                    Prénom *
                                </Label>
                                <Input
                                    id="first_name"
                                    type="text"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="given-name"
                                    value={data.first_name}
                                    onChange={(e) => setData('first_name', e.target.value)}
                                    disabled={processing}
                                    placeholder="Votre prénom"
                                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200"
                                />
                                <InputError message={errors.first_name} />
                            </div>

                            {/* Nom */}
                            <div>
                                <Label htmlFor="last_name" className="block text-sm font-medium text-gray-700 mb-2">
                                    Nom *
                                </Label>
                                <Input
                                    id="last_name"
                                    type="text"
                                    required
                                    tabIndex={2}
                                    autoComplete="family-name"
                                    value={data.last_name}
                                    onChange={(e) => setData('last_name', e.target.value)}
                                    disabled={processing}
                                    placeholder="Votre nom"
                                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200"
                                />
                                <InputError message={errors.last_name} />
                            </div>
                        </div>

                        {/* Email */}
                        <div>
                            <Label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-2">
                                Adresse E-mail *
                            </Label>
                            <Input
                                id="email"
                                type="email"
                                required
                                tabIndex={3}
                                autoComplete="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                disabled={processing}
                                placeholder="votre@email.com"
                                className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200"
                            />
                            <InputError message={errors.email} />
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {/* Téléphone */}
                            <div>
                                <Label htmlFor="mobile" className="block text-sm font-medium text-gray-700 mb-2">
                                    Téléphone *
                                </Label>
                                <Input
                                    id="mobile"
                                    type="tel"
                                    required
                                    tabIndex={4}
                                    autoComplete="tel"
                                    value={data.mobile}
                                    onChange={(e) => setData('mobile', e.target.value)}
                                    disabled={processing}
                                    placeholder="+33 6 12 34 56 78"
                                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200"
                                />
                                <InputError message={errors.mobile} />
                            </div>

                            {/* Date de naissance */}
                            <div>
                                <Label htmlFor="birth_date" className="block text-sm font-medium text-gray-700 mb-2">
                                    Date de naissance *
                                </Label>
                                <Input
                                    id="birth_date"
                                    type="date"
                                    required
                                    tabIndex={5}
                                    value={data.birth_date}
                                    onChange={(e) => setData('birth_date', e.target.value)}
                                    disabled={processing}
                                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200"
                                />
                                <InputError message={errors.birth_date} />
                            </div>
                        </div>

                        {/* Genre */}
                        <div>
                            <Label htmlFor="gender" className="block text-sm font-medium text-gray-700 mb-2">
                                Genre *
                            </Label>
                            <select
                                id="gender"
                                required
                                tabIndex={6}
                                value={data.gender}
                                onChange={(e) => setData('gender', e.target.value)}
                                disabled={processing}
                                className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200 bg-white"
                            >
                                <option value="">Sélectionnez votre genre</option>
                                <option value="male">Homme</option>
                                <option value="female">Femme</option>
                            </select>
                            <InputError message={errors.gender} />
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {/* Mot de passe */}
                            <div>
                                <Label htmlFor="password" className="block text-sm font-medium text-gray-700 mb-2">
                                    Mot de passe *
                                </Label>
                                <div className="relative">
                                    <Input
                                        id="password"
                                        type={showPassword ? "text" : "password"}
                                        required
                                        tabIndex={7}
                                        autoComplete="new-password"
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        disabled={processing}
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

                            {/* Confirmation mot de passe */}
                            <div>
                                <Label htmlFor="password_confirmation" className="block text-sm font-medium text-gray-700 mb-2">
                                    Confirmer le mot de passe *
                                </Label>
                                <div className="relative">
                                    <Input
                                        id="password_confirmation"
                                        type={showPasswordConfirmation ? "text" : "password"}
                                        required
                                        tabIndex={8}
                                        autoComplete="new-password"
                                        value={data.password_confirmation}
                                        onChange={(e) => setData('password_confirmation', e.target.value)}
                                        disabled={processing}
                                        placeholder="••••••••"
                                        className="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200"
                                    />
                                    <button
                                        type="button"
                                        onClick={() => setShowPasswordConfirmation(!showPasswordConfirmation)}
                                        className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 transition-colors"
                                    >
                                        {showPasswordConfirmation ? <EyeOff className="w-5 h-5" /> : <Eye className="w-5 h-5" />}
                                    </button>
                                </div>
                                <InputError message={errors.password_confirmation} />
                            </div>
                        </div>

                        {/* Conditions générales */}
                        <div className="text-sm text-gray-600 bg-gray-50 p-4 rounded-lg">
                            <p className="mb-2">
                                En créant un compte, vous acceptez nos{' '}
                                <a href="/conditions-service" className="text-orange-600 hover:text-orange-700 underline">Conditions générales</a>{' '}
                                et notre{' '}
                                <a href="/politique-donnees" className="text-orange-600 hover:text-orange-700 underline">Politique de confidentialité</a>.
                            </p>
                        </div>

                        {/* Bouton d'inscription */}
                        <Button
                            type="submit"
                            className="w-full bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 transform hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                            tabIndex={9}
                            disabled={processing}
                        >
                            {processing ? (
                                <div className="flex items-center justify-center">
                                    <LoaderCircle className="h-5 w-5 animate-spin mr-2" />
                                    Création du compte...
                                </div>
                            ) : (
                                'Créer mon compte'
                            )}
                        </Button>

                        {/* Lien de connexion */}
                        <div className="text-center pt-4 border-t border-gray-200">
                            <p className="text-gray-600">
                                Vous avez déjà un compte ?{' '}
                                <TextLink
                                    href={route('login')}
                                    className="text-orange-600 hover:text-orange-700 font-semibold"
                                    tabIndex={6}
                                >
                                    Se connecter
                                </TextLink>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
}
