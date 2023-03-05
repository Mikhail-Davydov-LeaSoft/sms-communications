import {onBeforeUnmount} from "vue";

let useKeyDown = (keyCombos: Array<{key: string, fn: Function}>) => {
    let onKeyDown = (event: KeyboardEvent) => {
        let kc = keyCombos.find((kc) => kc.key === event.key);

        if(kc) {
            kc.fn();
        }
    }

    window.addEventListener('keydown', onKeyDown);
    onBeforeUnmount(() => {
        window.removeEventListener('keydown', onKeyDown);
    });
};

export default useKeyDown;
