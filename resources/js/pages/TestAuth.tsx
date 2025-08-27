import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';

export default function TestAuth() {
    const [message, setMessage] = useState('');
    
    const { data, setData, post, processing, errors } = useForm({
        email: 'test@test.com',
        password: 'password',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        
        post('/login', {
            onSuccess: () => {
                setMessage('✅ Connexion réussie !');
            },
            onError: (errors) => {
                console.error('Erreurs de connexion:', errors);
                setMessage('❌ Erreur de connexion: ' + JSON.stringify(errors));
            }
        });
    };

    const testCsrf = async () => {
        try {
            const response = await fetch('/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    email: 'test@test.com',
                    password: 'password'
                })
            });
            
            const result = await response.text();
            setMessage(`Status: ${response.status} - ${result.substring(0, 200)}`);
        } catch (error) {
            setMessage('Erreur fetch: ' + error);
        }
    };

    return (
        <>
            <Head title="Test Authentification" />
            
            <div className="min-h-screen bg-gray-100 flex items-center justify-center">
                <div className="max-w-md w-full bg-white rounded-lg shadow-md p-6">
                    <h1 className="text-2xl font-bold mb-6 text-center">🧪 Test Authentification</h1>
                    
                    <div className="mb-6 p-4 bg-blue-50 rounded">
                        <h3 className="font-semibold text-blue-800 mb-2">Informations de test</h3>
                        <p className="text-blue-600">Email: test@test.com</p>
                        <p className="text-blue-600">Password: password</p>
                        <p className="text-sm text-blue-500 mt-2">
                            CSRF Token: {document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')?.substring(0, 20)}...
                        </p>
                    </div>
                    
                    <form onSubmit={submit} className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700">Email</label>
                            <input
                                type="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            />
                            {errors.email && <p className="text-red-500 text-sm mt-1">{errors.email}</p>}
                        </div>
                        
                        <div>
                            <label className="block text-sm font-medium text-gray-700">Password</label>
                            <input
                                type="password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            />
                            {errors.password && <p className="text-red-500 text-sm mt-1">{errors.password}</p>}
                        </div>
                        
                        <div className="space-y-2">
                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 disabled:opacity-50"
                            >
                                {processing ? 'Connexion...' : 'Se connecter (Inertia)'}
                            </button>
                            
                            <button
                                type="button"
                                onClick={testCsrf}
                                className="w-full bg-green-500 text-white py-2 px-4 rounded-md hover:bg-green-600"
                            >
                                Test Fetch Direct
                            </button>
                        </div>
                    </form>
                    
                    {message && (
                        <div className="mt-4 p-3 bg-gray-50 rounded border">
                            <p className="text-sm">{message}</p>
                        </div>
                    )}
                    
                    <div className="mt-6 text-center">
                        <a href="/register" className="text-blue-500 hover:underline">
                            Créer un compte
                        </a>
                        {' | '}
                        <a href="/dashboard" className="text-blue-500 hover:underline">
                            Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </>
    );
}
