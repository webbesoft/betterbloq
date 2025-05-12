export const debounce = (func, delay) => {
    let timeoutId: string | number | NodeJS.Timeout | undefined;

    return function (...args: any) {
        clearTimeout(timeoutId); // Clear any existing timer
        timeoutId = setTimeout(() => {
            func.apply(this, args); // Execute the function after the delay
        }, delay);
    };
};

export const formatDate = (dateString: string | null | undefined): string => {
    if (!dateString) return 'N/A';
    try {
        return new Date(dateString).toLocaleDateString(undefined, {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    } catch (e) {
        return 'Invalid Date';
    }
};

export const parseAndCompareDates = (
    dateStr1: string | null | undefined,
    dateStr2: string | null | undefined,
    dayThreshold: number = 0,
): { needsStorage: boolean; daysDifference: number } => {
    if (!dateStr1 || !dateStr2) {
        return { needsStorage: false, daysDifference: 0 };
    }
    try {
        const date1 = new Date(dateStr1);
        const date2 = new Date(dateStr2);

        const utcDate1 = new Date(Date.UTC(date1.getUTCFullYear(), date1.getUTCMonth(), date1.getUTCDate()));
        const utcDate2 = new Date(Date.UTC(date2.getUTCFullYear(), date2.getUTCMonth(), date2.getUTCDate()));

        const differenceInMilliseconds = utcDate1.getTime() - utcDate2.getTime();
        const differenceInDays = differenceInMilliseconds / (1000 * 60 * 60 * 24);

        return {
            needsStorage: differenceInDays > dayThreshold,
            // could be Math.floor, if we don't want to count partial days
            daysDifference: Math.ceil(differenceInDays),
        };
    } catch (error) {
        console.error('Error parsing or comparing dates:', error);
        return { needsStorage: false, daysDifference: 0 };
    }
};
