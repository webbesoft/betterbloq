import { type BreadcrumbItem, type SharedData } from '@/types';
import { Transition } from '@headlessui/react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler } from 'react';

import DeleteUser from '@/components/delete-user';
import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

interface ProfileForm {
    name: string;
    email: string;
    address_line_1: string;
    address_line_2: string;
    city?: string;
    state?: string;
    postal_code?: string;
    country?: string;
}

export default function Profile({ mustVerifyEmail, status }: { mustVerifyEmail: boolean; status?: string }) {
    const { auth } = usePage<SharedData>().props;

    const { data, setData, patch, errors, processing, recentlySuccessful } = useForm<Required<ProfileForm>>({
        name: auth.user.name,
        email: auth.user.email,
        address_line_1: auth.user.address_line_1 || '',
        address_line_2: auth.user.address_line_2 || '',
        city: auth.user.city || '',
        state: auth.user.state || '',
        postal_code: auth.user.postal_code || '',
        country: auth.user.country_code || '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        patch(route('profile.update'), {
            preserveScroll: true,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Profile settings" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall title="Profile information" description="Update your name and email address" />

                    <form onSubmit={submit} className="space-y-6">
                        <div className="grid gap-2">
                            <Label htmlFor="name" className={'label'}>
                                Name
                            </Label>

                            <Input
                                id="name"
                                className="mt-1 block w-full"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                required
                                autoComplete="name"
                                placeholder="Full name"
                            />

                            <InputError className="mt-2" message={errors.name} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="email" className={'label'}>
                                Email address
                            </Label>

                            <Input
                                id="email"
                                type="email"
                                className="mt-1 block w-full"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                required
                                autoComplete="username"
                                placeholder="Email address"
                            />

                            <InputError className="error-text mt-2" message={errors.email} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="address_line_1" className={'label'}>
                                Address Line 1
                            </Label>
                            <Input
                                id="address_line_1"
                                className="mt-1 block w-full"
                                value={data.address_line_1}
                                onChange={(e) => setData('address_line_1', e.target.value)}
                                autoComplete="street-address"
                                placeholder="123 Main St"
                                required
                            />
                            <InputError className="mt-2" message={errors.address_line_1} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="address_line_2" className={'label'}>
                                Address Line 2
                            </Label>
                            <Input
                                id="address_line_2"
                                className="mt-1 block w-full"
                                value={data.address_line_2}
                                onChange={(e) => setData('address_line_2', e.target.value)}
                                autoComplete="street-address"
                                placeholder="123 Main St"
                            />
                            <InputError className="mt-2" message={errors.address_line_1} />
                        </div>

                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div className="grid gap-2">
                                <Label htmlFor="city" className={'label'}>
                                    City
                                </Label>
                                <Input
                                    id="city"
                                    className="mt-1 block w-full"
                                    value={data.city}
                                    onChange={(e) => setData('city', e.target.value)}
                                    autoComplete="address-level2" // For city
                                    placeholder="Anytown"
                                    required
                                />
                                <InputError className="mt-2" message={errors.city} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="state" className={'label'}>
                                    State / Province
                                </Label>
                                <Input
                                    id="state"
                                    className="mt-1 block w-full"
                                    value={data.state}
                                    onChange={(e) => setData('state', e.target.value)}
                                    autoComplete="address-level1" // For state/province
                                    placeholder="CA"
                                    required
                                />
                                <InputError className="mt-2" message={errors.state} />
                            </div>
                        </div>

                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div className="grid gap-2">
                                <Label htmlFor="postal_code" className={'label'}>
                                    Postal Code
                                </Label>
                                <Input
                                    id="postal_code"
                                    className="mt-1 block w-full"
                                    value={data.postal_code}
                                    onChange={(e) => setData('postal_code', e.target.value)}
                                    autoComplete="postal-code"
                                    placeholder="90210"
                                />
                                <InputError className="mt-2" message={errors.postal_code} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="country" className={'label'}>
                                    Country
                                </Label>
                                <Input
                                    id="country"
                                    className="mt-1 block w-full"
                                    value={data.country}
                                    onChange={(e) => setData('country', e.target.value)}
                                    autoComplete="country-code"
                                    placeholder="USA"
                                />
                                <InputError className="mt-2" message={errors.country} />
                            </div>
                        </div>

                        {mustVerifyEmail && auth.user.email_verified_at === null && (
                            <div>
                                <p className="text-muted-foreground -mt-4 text-sm">
                                    Your email address is unverified.{' '}
                                    <Link
                                        href={route('verification.send')}
                                        method="post"
                                        as="button"
                                        className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                    >
                                        Click here to resend the verification email.
                                    </Link>
                                </p>

                                {status === 'verification-link-sent' && (
                                    <div className="mt-2 text-sm font-medium text-green-600">
                                        A new verification link has been sent to your email address.
                                    </div>
                                )}
                            </div>
                        )}

                        <div className="flex items-center gap-4">
                            <Button disabled={processing}>Save</Button>

                            <Transition
                                show={recentlySuccessful}
                                enter="transition ease-in-out"
                                enterFrom="opacity-0"
                                leave="transition ease-in-out"
                                leaveTo="opacity-0"
                            >
                                <p className="text-sm text-neutral-600">Saved</p>
                            </Transition>
                        </div>
                    </form>
                </div>

                <DeleteUser />
            </SettingsLayout>
        </AppLayout>
    );
}
