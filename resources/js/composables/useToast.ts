// Toast utility for showing notifications
export function useToast() {
    const showToast = (message: string, type: 'success' | 'error' | 'info' = 'info', duration = 3000) => {
        window.dispatchEvent(
            new CustomEvent('show-toast', {
                detail: { message, type, duration },
            }),
        );
    };

    return {
        success: (message: string, duration?: number) => showToast(message, 'success', duration),
        error: (message: string, duration?: number) => showToast(message, 'error', duration),
        info: (message: string, duration?: number) => showToast(message, 'info', duration),
    };
}
