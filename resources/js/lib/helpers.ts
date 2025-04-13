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
            year: 'numeric', month: 'short', day: 'numeric'
        });
    } catch (e) {
        return 'Invalid Date';
    }
};