import { useState, useRef, useLayoutEffect, useEffect } from 'react';
import { createPortal } from 'react-dom';
import { CalendarDays, ChevronDown, ChevronLeft, ChevronRight } from 'lucide-react';

const TEAL = '#1d6058';
const MUTED = '#a0a0a0';

/** Returns an ISO date key (YYYY-MM-DD) from a Date, using local time. */
function dateKey(date: Date): string {
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
}

/** Formats an ISO date string for display, e.g. "Jan 5, 2026". */
function displayDate(value: string): string {
    if (!value) return 'Select date';
    return new Intl.DateTimeFormat('en-PH', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    }).format(new Date(`${value}T00:00:00`));
}

export function DarkDatePicker({
    label,
    value,
    minimumDate,
    onChange,
    isOpen,
    onOpenChange,
    align = 'start',
    required,
    id,
    name,
    className,
    placeholder,
}: {
    label?: React.ReactNode;
    value: string;
    minimumDate?: string;
    onChange: (value: string) => void;
    isOpen: boolean;
    onOpenChange: (isOpen: boolean) => void;
    align?: 'start' | 'end';
    required?: boolean;
    id?: string;
    name?: string;
    className?: string;
    placeholder?: string;
}) {
    const [visibleMonth, setVisibleMonth] = useState(
        () => new Date(`${value || minimumDate || dateKey(new Date())}T00:00:00`),
    );
    const containerRef = useRef<HTMLDivElement>(null);
    const triggerRef = useRef<HTMLButtonElement>(null);
    const popoverRef = useRef<HTMLDivElement>(null);
    const [coords, setCoords] = useState({ top: 0, left: 0, width: 0 });

    // Recompute the popover's fixed-position coordinates from the
    // trigger button's on-screen rect. Runs on open, and again on
    // scroll/resize so the popover tracks the button instead of
    // drifting away from it.
    useLayoutEffect(() => {
        if (!isOpen) return;

        function updateCoords() {
            const rect = triggerRef.current?.getBoundingClientRect();
            if (!rect) return;
            setCoords({
                top: rect.bottom + 8,
                left: align === 'end' ? rect.right : rect.left,
                width: rect.width,
            });
        }

        updateCoords();
        window.addEventListener('scroll', updateCoords, true);
        window.addEventListener('resize', updateCoords);
        return () => {
            window.removeEventListener('scroll', updateCoords, true);
            window.removeEventListener('resize', updateCoords);
        };
    }, [isOpen, align]);

    // Close on outside click. Checks both the trigger container AND
    // the portaled popover, since the popover no longer lives inside
    // containerRef in the DOM tree.
    useEffect(() => {
        if (!isOpen) return;
        function handleClick(event: MouseEvent) {
            const target = event.target as Node;
            if (
                containerRef.current &&
                !containerRef.current.contains(target) &&
                popoverRef.current &&
                !popoverRef.current.contains(target)
            ) {
                onOpenChange(false);
            }
        }
        document.addEventListener('mousedown', handleClick);
        return () => document.removeEventListener('mousedown', handleClick);
    }, [isOpen, onOpenChange]);

    // Keep the visible month in sync if the value changes externally
    // (e.g. check-out auto-adjusting when check-in changes).
    useEffect(() => {
        if (value) setVisibleMonth(new Date(`${value}T00:00:00`));
    }, [value]);

    const minimum = minimumDate ? new Date(`${minimumDate}T00:00:00`) : new Date(0);
    const daysInMonth = new Date(
        visibleMonth.getFullYear(),
        visibleMonth.getMonth() + 1,
        0,
    ).getDate();
    const leadingDays = new Date(
        visibleMonth.getFullYear(),
        visibleMonth.getMonth(),
        1,
    ).getDay();
    const monthName = visibleMonth.toLocaleString('en-PH', {
        month: 'long',
        year: 'numeric',
    });

    function selectDate(date: Date) {
        onChange(dateKey(date));
        onOpenChange(false);
    }

    function goToPreviousMonth() {
        setVisibleMonth(
            new Date(
                visibleMonth.getFullYear(),
                visibleMonth.getMonth() - 1,
                1,
            ),
        );
    }
    
    function goToNextMonth() {
        setVisibleMonth(
            new Date(
                visibleMonth.getFullYear(),
                visibleMonth.getMonth() + 1,
                1,
            ),
        );
    }

    return (
        <div
            ref={containerRef}
            className={`relative flex flex-col gap-1.5 text-sm font-medium text-foreground w-full ${className || ''}`}
        >
            {name && (
                <input type="hidden" id={id} name={name} value={value} required={required} />
            )}
            {label}
            <button
                ref={triggerRef}
                type="button"
                onClick={() => onOpenChange(!isOpen)}
                aria-expanded={isOpen}
                className={`flex w-full items-center justify-between gap-2 rounded-md border bg-transparent px-3 py-1 text-left text-sm font-normal transition outline-none shadow-xs h-9 ${isOpen ? 'ring-1 ring-ring border-ring' : 'border-input'} ${!value ? 'text-muted-foreground' : 'text-foreground'}`}
            >
                <span className="flex items-center gap-2">
                    <CalendarDays
                        className="size-4"
                        style={{ color: TEAL }}
                    />
                    {displayDate(value) || placeholder}
                </span>
                <ChevronDown
                    className={`size-4 transition ${isOpen ? 'rotate-180' : ''}`}
                    style={{ color: TEAL }}
                />
            </button>

            {isOpen &&
                typeof document !== 'undefined' &&
                createPortal(
                    <div
                        ref={popoverRef}
                        className="fixed z-[9999] w-[min(20rem,calc(100vw-3rem))] rounded-md border border-white/10 p-4 shadow-xl"
                        style={{
                            backgroundColor: '#161616',
                            top: coords.top,
                            left:
                                align === 'end'
                                    ? coords.left - 320
                                    : coords.left,
                        }}
                    >
                        <div className="mb-4 flex items-center justify-between">
                            <button
                                type="button"
                                onClick={goToPreviousMonth}
                                className="flex size-8 items-center justify-center rounded-full text-white/70 hover:bg-white/5"
                                aria-label="Previous month"
                            >
                                <ChevronLeft className="size-4" />
                            </button>
                            <span className="text-sm font-semibold text-white">
                                {monthName}
                            </span>
                            <button
                                type="button"
                                onClick={goToNextMonth}
                                className="flex size-8 items-center justify-center rounded-full text-white/70 hover:bg-white/5"
                                aria-label="Next month"
                            >
                                <ChevronRight className="size-4" />
                            </button>
                        </div>

                        <div
                            className="grid grid-cols-7 gap-1 text-center text-[11px] font-semibold"
                            style={{ color: MUTED }}
                        >
                            {['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'].map(
                                (day) => (
                                    <span key={day} className="py-1">
                                        {day}
                                    </span>
                                ),
                            )}
                        </div>

                        <div className="grid grid-cols-7 gap-1">
                            {Array.from({ length: leadingDays }).map(
                                (_, index) => (
                                    <span key={`blank-${index}`} />
                                ),
                            )}

                            {Array.from({ length: daysInMonth }, (_, index) => {
                                const date = new Date(
                                    visibleMonth.getFullYear(),
                                    visibleMonth.getMonth(),
                                    index + 1,
                                );
                                const key = dateKey(date);
                                const isPast = date < minimum;
                                const selected = key === value;

                                let classes = 'text-white/80 hover:bg-white/5';
                                if (selected) {
                                    classes = 'font-semibold text-white';
                                } else if (isPast) {
                                    classes =
                                        'cursor-not-allowed text-white/20 hover:bg-transparent';
                                }

                                return (
                                    <button
                                        key={key}
                                        type="button"
                                        disabled={isPast}
                                        onClick={() => selectDate(date)}
                                        style={
                                            selected
                                                ? { backgroundColor: TEAL }
                                                : undefined
                                        }
                                        className={`flex aspect-square items-center justify-center rounded-full text-xs transition ${classes}`}
                                    >
                                        {index + 1}
                                    </button>
                                );
                            })}
                        </div>

                        <div className="mt-4 flex items-center justify-between border-t border-white/10 pt-3">
                            <button
                                type="button"
                                onClick={() => {
                                    const now = new Date();
                                    setVisibleMonth(now);
                                    if (now >= minimum) selectDate(now);
                                }}
                                className="text-[11px] font-semibold hover:underline"
                                style={{ color: TEAL }}
                            >
                                Select today
                            </button>
                        </div>
                    </div>,
                    document.body,
                )}
        </div>
    );
}

export function DatePicker({
    id,
    name,
    value,
    onChange,
    required,
    className,
    placeholder = 'Pick a date',
    minimumDate,
}: {
    id?: string;
    name?: string;
    value?: string;
    onChange?: (date: string) => void;
    required?: boolean;
    className?: string;
    placeholder?: string;
    minimumDate?: string;
}) {
    const [internalValue, setInternalValue] = useState(value || '');
    const [isOpen, setIsOpen] = useState(false);

    const handleChange = (newVal: string) => {
        setInternalValue(newVal);
        onChange?.(newVal);
    };

    return (
        <DarkDatePicker
            id={id}
            name={name}
            value={internalValue}
            onChange={handleChange}
            isOpen={isOpen}
            onOpenChange={setIsOpen}
            required={required}
            className={className}
            placeholder={placeholder}
            minimumDate={minimumDate}
        />
    );
}
