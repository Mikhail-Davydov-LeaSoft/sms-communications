declare module 'emoji-mart-vue-fast' {
    import { Component } from "vue";

    export interface EmojiIndexOptions {
        emojisToShowFilter: (emoji: any) => boolean,
        include: Array<string>,
        exclude: Array<string>,
        custom: Array<any>,
        recent: Array<string>,
        recentLength?: number,
    }

    export class EmojiIndex {
        constructor(data: any, options?: EmojiIndexOptions);
    }

    export interface EmojiView {
        native: string;
    }

    export const Picker: Component;
}
