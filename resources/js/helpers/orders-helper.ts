export const getOrderPaymentStatusText = (status: string): string => {
    // options: 'created', 'payment_authorized', 'pending_finalization', 'processing_capture', 'completed', 'capture_failed', 'cancelled'
    switch (status.toLocaleLowerCase()) {
        case 'created':
            return 'Created';
        case 'paid':
            return 'Paid; awaiting finalization';
        case 'refund_failed':
            return 'Paid; refund needs attention';
        case 'payment_authorized':
            return 'Authorized';
        case 'pending_finalization':
            return 'Pending Finalization';
        case 'processing_capture':
            return 'Finalizing Payment';
        case 'completed':
            return 'Completed';
        case 'capture_failed':
            return 'Failed';
        case 'cancelled':
            return 'Cancelled';
        default:
            return 'Unknown';
    }
};

export const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
};
