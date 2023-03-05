import {Ref, ref} from "vue";

interface ModalSettings {
    onShow?: (...args: any[]) => void | null | boolean,
    onHide?: (...args: any[]) => void | null | boolean,
    onShown?: (...args: any[]) => void | null,
    onHidden?: (...args: any[]) => void | null,
}

interface UseModalReturn {
    active: Ref<boolean>,
    show: (...args: any[]) => void,
    hide: (...args: any[]) => void,
}

    export function useModal(options: ModalSettings = {}): UseModalReturn {
    const  active = ref(false),
        show = (...args: any[]) => {
            if (options.onShow && options.onShow(...args) === false) return;

            active.value = true;
            if (options.onShown) options.onShown(...args);
        },
        hide = (...args: any[]) => {
            if (options.onHide && options.onHide(...args) === false) return;

            active.value = false;
            if (options.onHidden) options.onHidden(...args);
        };

    return {
        active,
        show,
        hide,
    };
}
