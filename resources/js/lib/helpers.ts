export const debounce = (func, delay) => {
    let timeoutId: string | number | NodeJS.Timeout | undefined;

    return function (...args: any) {
        clearTimeout(timeoutId); // Clear any existing timer
        timeoutId = setTimeout(() => {
            func.apply(this, args); // Execute the function after the delay
        }, delay);
    };
};
