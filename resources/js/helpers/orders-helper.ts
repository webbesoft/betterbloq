export const getOrderPaymentStatusText = (status: string): string => {
    // options: 'created', 'payment_authorized', 'pending_finalization', 'processing_capture', 'completed', 'capture_failed', 'cancelled'
    switch (status.toLocaleLowerCase()) {
        case 'created':
            return 'Created';
        case 'payment_authorized':
            return 'Authorized';
        case 'pending_finalization':
            return 'Pending Finalization';
        case 'processing_capture':
            return 'Processing Capture';
        case 'completed':
            return 'Completed';
        case 'capture_failed':
            return 'Failed';
        default:
            return 'Cancelled';
    }
};

export const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
};
