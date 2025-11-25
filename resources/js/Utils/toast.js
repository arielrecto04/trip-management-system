import { useToast } from 'primevue/usetoast';

export function useGlobalToast() {
    const toast = useToast();

    function success(message) {
        toast.add({ severity: 'success', summary: 'Success', detail: message, life: 5000 });
    }

    function error(message) {
        toast.add({ severity: 'error', summary: 'Error', detail: message, life: 5000 });
    }

    return { success, error };
}
