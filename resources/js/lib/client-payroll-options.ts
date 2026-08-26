export type Option = { value: string; label: string };

export const PAYROLL_PERIOD_OPTIONS: Option[] = [
    { value: 'weekly', label: 'Weekly' },
    { value: 'semi_monthly', label: 'Semi-monthly' },
    { value: 'monthly', label: 'Monthly' },
];

export const PAYROLL_FREQUENCY_OPTIONS: Option[] = [
    { value: 'weekly', label: 'Weekly' },
    { value: 'bi_weekly', label: 'Bi-weekly' },
    { value: 'semi_monthly', label: 'Semi-monthly' },
    { value: 'monthly', label: 'Monthly' },
];

export const CUTOFF_TYPE_OPTIONS: Option[] = [
    { value: 'fixed_days', label: 'Fixed Days' },
    { value: 'end_of_month', label: 'End of Month' },
];

export function optionLabel(options: Option[], value: string | null): string {
    if (!value) {
        return '—';
    }

    return options.find((option) => option.value === value)?.label ?? value;
}
