import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';

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

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <AuthLayout title="Create an account" description="Enter your details below to create your account">
            <Head title="Register" />
            <form className="flex flex-col gap-6" onSubmit={submit}>
                <div className="grid gap-6">
                    {/* Prénom */}
                    <div className="grid gap-2">
                        <Label htmlFor="first_name">Prénom *</Label>
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
                        />
                        <InputError message={errors.first_name} className="mt-2" />
                    </div>

                    {/* Nom */}
                    <div className="grid gap-2">
                        <Label htmlFor="last_name">Nom *</Label>
                        <Input
                            id="last_name"
                            type="text"
                            required
                            tabIndex={2}
                            autoComplete="family-name"
                            value={data.last_name}
                            onChange={(e) => setData('last_name', e.target.value)}
                            disabled={processing}
                            placeholder="Votre nom de famille"
                        />
                        <InputError message={errors.last_name} className="mt-2" />
                    </div>

                    {/* Adresse E-mail */}
                    <div className="grid gap-2">
                        <Label htmlFor="email">Adresse E-mail *</Label>
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
                        />
                        <InputError message={errors.email} />
                    </div>

                    {/* Numéro mobile */}
                    <div className="grid gap-2">
                        <Label htmlFor="mobile">Numéro mobile *</Label>
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
                        />
                        <InputError message={errors.mobile} />
                    </div>

                    {/* Date de naissance */}
                    <div className="grid gap-2">
                        <Label htmlFor="birth_date">Date de naissance *</Label>
                        <Input
                            id="birth_date"
                            type="date"
                            required
                            tabIndex={5}
                            value={data.birth_date}
                            onChange={(e) => setData('birth_date', e.target.value)}
                            disabled={processing}
                        />
                        <InputError message={errors.birth_date} />
                    </div>

                    {/* Genre */}
                    <div className="grid gap-2">
                        <Label htmlFor="gender">Genre *</Label>
                        <select
                            id="gender"
                            required
                            tabIndex={6}
                            value={data.gender}
                            onChange={(e) => setData('gender', e.target.value)}
                            disabled={processing}
                            className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <option value="">Sélectionnez votre genre</option>
                            <option value="male">Homme (H)</option>
                            <option value="female">Femme (F)</option>
                        </select>
                        <InputError message={errors.gender} />
                    </div>

                    {/* Mot de passe */}
                    <div className="grid gap-2">
                        <Label htmlFor="password">Mot de passe *</Label>
                        <Input
                            id="password"
                            type="password"
                            required
                            tabIndex={7}
                            autoComplete="new-password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            disabled={processing}
                            placeholder="Votre mot de passe"
                        />
                        <InputError message={errors.password} />
                    </div>

                    {/* Confirmation mot de passe */}
                    <div className="grid gap-2">
                        <Label htmlFor="password_confirmation">Confirmer le mot de passe *</Label>
                        <Input
                            id="password_confirmation"
                            type="password"
                            required
                            tabIndex={8}
                            autoComplete="new-password"
                            value={data.password_confirmation}
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                            disabled={processing}
                            placeholder="Confirmez votre mot de passe"
                        />
                        <InputError message={errors.password_confirmation} />
                    </div>

                    {/* Conditions générales */}
                    <div className="grid gap-3 text-sm text-muted-foreground">
                        <p>
                            Les membres qui utilisent ce service ont accès à vos coordonnées sur YAMSOO.{' '}
                            <a href="/cgu" className="text-primary hover:underline">En savoir plus</a>
                        </p>
                        <p>
                            En appuyant sur S'inscrire, vous acceptez nos{' '}
                            <a href="/conditions-service" className="text-primary hover:underline">Conditions générales</a>,{' '}
                            notre{' '}
                            <a href="/politique-donnees" className="text-primary hover:underline">Politique d'utilisation des données</a>{' '}
                            et notre{' '}
                            <a href="/politique-cookies" className="text-primary hover:underline">Politique d'utilisation des cookies</a>.{' '}
                            Vous recevrez peut-être des notifications par SMS ou par Mail de notre part et vous pouvez à tout moment vous désabonner.
                        </p>
                    </div>

                    <Button type="submit" className="mt-4 w-full" tabIndex={9} disabled={processing}>
                        {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                        S'inscrire
                    </Button>
                </div>

                <div className="text-center text-sm text-muted-foreground">
                    Already have an account?{' '}
                    <TextLink href={route('login')} tabIndex={6}>
                        Log in
                    </TextLink>
                </div>
            </form>
        </AuthLayout>
    );
}
