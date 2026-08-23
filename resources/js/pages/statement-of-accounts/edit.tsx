import { Form, Head, Link, usePage } from '@inertiajs/react';
import AccountReceivableController from '@/actions/App/Http/Controllers/AccountReceivableController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index, show } from '@/routes/statement-of-accounts';

type SoaFormData = {
    id: number;
    account_receivable_number: string;
    client_name: string;
    account_receivable_date: string;
    subtotal: number;
    tax_amount: number;
    total_amount: number;
    status: string;
    rejection_reason: string | null;
};

type PageProps = {
    soa: SoaFormData;
};

export default function StatementOfAccountsEdit() {
    const { soa } = usePage<PageProps>().props;

    return (
        <>
            <Head title={`Edit ${soa.account_receivable_number}`} />

            <div className="max-w-2xl space-y-6 p-4">
                <Heading
                    title={`Edit ${soa.account_receivable_number}`}
                    description={`Client: ${soa.client_name}`}
                />

                {soa.rejection_reason && (
                    <Alert variant="destructive">
                        <AlertTitle>Rejection Feedback from GM</AlertTitle>
                        <AlertDescription>{soa.rejection_reason}</AlertDescription>
                    </Alert>
                )}

                <Form {...AccountReceivableController.update.form(soa.id)} className="space-y-6">
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="account_receivable_date">Statement Date</Label>
                                <Input
                                    id="account_receivable_date"
                                    name="account_receivable_date"
                                    type="date"
                                    required
                                    defaultValue={soa.account_receivable_date}
                                />
                                <InputError message={errors.account_receivable_date} />
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="subtotal">Subtotal Amount (PHP)</Label>
                                    <Input
                                        id="subtotal"
                                        name="subtotal"
                                        type="number"
                                        step="0.01"
                                        min={0}
                                        required
                                        defaultValue={soa.subtotal}
                                    />
                                    <InputError message={errors.subtotal} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="tax_amount">Tax / VAT Amount (PHP)</Label>
                                    <Input
                                        id="tax_amount"
                                        name="tax_amount"
                                        type="number"
                                        step="0.01"
                                        min={0}
                                        required
                                        defaultValue={soa.tax_amount}
                                    />
                                    <InputError message={errors.tax_amount} />
                                </div>
                            </div>

                            <div className="flex items-center gap-4">
                                <Button disabled={processing}>Save Changes</Button>

                                <Button variant="secondary" asChild>
                                    <Link href={show(soa.id)}>Cancel</Link>
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

StatementOfAccountsEdit.layout = {
    breadcrumbs: [
        {
            title: 'Statements of Account',
            href: index(),
        },
        {
            title: 'Edit SOA',
            href: index(),
        },
    ],
};
