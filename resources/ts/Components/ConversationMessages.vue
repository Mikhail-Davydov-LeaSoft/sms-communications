<template>
    <div class="d-flex flex-column overflow-hidden h-100 wrap">
        <div class="d-flex justify-content-between py-3 border-top border-bottom">
            <div class="d-flex align-self-baseline">
                <div class="font-weight-bold mr-2" style="font-size: 22px">
                    <template v-if="conversation.phone_number.contact">
                        {{conversation.phone_number.contact.name}}
                    </template>
                    <template v-else>
                        {{conversation.phone_number.value}}
                    </template>
                </div>
                <div class="py-1 mr-5" type='button' @click="blockContact()">
                    <template v-if="conversation.phone_number.blocked_at === null">
                        <img class="" :src="getIconUrl('block.png')" width="20" height="20">
                        <span>Block</span>
                    </template>
                    <template v-else>
                        <img class="" :src="getIconUrl('unblock.png')" width="20" height="20">
                        <span class="text-danger">Unblock</span>
                    </template>
                </div>
                <div class="py-1 text-success mr-3" type='button' @click="selectedMode = !selectedMode; resetSelected();">
                    <img class="" :src="getIconUrl('select_messages.png')" width="20" height="20">
                    <span v-if="!selectedMode">Select messages</span>
                    <span v-else>Clear selection</span>
                </div>
                <div v-if="selectedMode" class="py-1 mr-3">
                    <span>{{numberSelectedMessages}}</span>
                    <span v-if="numberSelectedMessages > 1 || numberSelectedMessages == 0">Messages</span>
                    <span v-else>Message</span>
                </div>
                <button v-if="selectedMode" :disabled="numberSelectedMessages == 0" class="msgButton deleteButton py-1 align-self-baseline" @click="deleteMessages">Delete</button>
                <button v-if="selectedMode" :disabled="numberSelectedMessages == 0" class="msgButton unreadButton py-1 align-self-baseline" @click="unreadMessages">Unread</button>
                <button v-if="selectedMode" :disabled="numberSelectedMessages == 0" class="msgButton pinButton py-1 align-self-baseline" @click="pinMessages">Pin</button>
            </div>
            <div class="d-flex ml-4">
                <div>
                    <div v-if="getPinnedMessages.length > 0" class="pinnedMessagesBlock overflow-auto p-1">
                        <div :value="message.id" v-for="(message, index) in pinnedMessages">
                            <div class="d-flex justify-content-between">
                                <div @click="scrollToMessage(message.id)" class="message">
                                    <div class="font-weight-bold">Pinned message #{{index+1}}</div>
                                    <template v-if="message.message_type == 'text'">{{shortMessage(message.body)}}</template>
                                    <template v-else-if="message.message_type == 'image'">
                                        <span class="">Image</span>
                                        <img :src="getMmsUrl(message.file_name)" width="20px" height="20px" />
                                    </template>
                                </div>
                                <button @click="unpinMessage(message.id)" class="unpinned"></button>
                            </div>
                        </div>
                    </div>
                </div>
                <button @click="unpinAllMessages" class="unpinnedAll align-self-center text-danger ml-3" v-if="pinnedMessages.length > 0">Unpinned all</button>
            </div>
        </div>
        <div class="correspondence pt-3 border-bottom" id="correspondence" scroll-region>
            <message
                :ref="(el) => setMessageComponents(message.id, el)"
                v-for="(message, index) in conversation.messages"
                :selected-mode="selectedMode"
                :key="index"
                :message="message"
                :index="index"
                :conversation="conversation"
                :selected-messages="selectedMessages"
                @openViewAttachmentModal="(message) => openViewAttachmentModal(message)"
                @selectedMessageId="(messageId) => selectedMessages.push(messageId)"
                @removedMessageId="(messageId) => excludeMessageId(messageId)"
            >
            </message>
            <view-attachment-modal
                ref="viewAttachmentModal"
            >
            </view-attachment-modal>
        </div>
        <div v-if="conversation.phone_number.blocked_at === null" class="type_msg fixed-bottom offset-4">
            <div class="input_msg_write mb-3 ml-3">
                <div class="row" ref="picker">
                    <Picker
                        v-if="isPickerVisible"
                        :data="emojiIndex"
                        set="twitter"
                        @select="showEmoji"
                        :autoFocus="true"
                    />
                </div>
                <p class="text-danger mb-0" style="font-size: 21px; min-height: 30px">{{sendingMessageError}}</p>
                <textarea ref="smsTextArea" v-model="msgData" @keydown="inputHandler" class="write_msg border-top border-bottom pt-3 px-3 mb-3" placeholder="Start typing..." />
                <div class="d-flex justify-content-between">
                    <div class="d-flex ml-3">
                        <a class="mr-3" @click="isPickerVisible = ! isPickerVisible">
                            <img class="" :src="getIconUrl('emojis.png')" width="30" height="30">
                        </a>
                        <a>
                            <input type="file" class="sms_attach" id="sms_attach" ref="file" v-on:change="handleFileUpload">
                            <label for="sms_attach" class="custom_sms_attach">&nbsp;</label>
                        </a>
                    </div>
                    <div class="d-flex mr-5">
                        <span class="pt-2">Shift + Enter</span>
                        <button @click="sendMessage" :disabled="! msgData" class="msg_send_btn ml-3 bg-primary text-white" style="width: 170px" type="button">Send</button>
                    </div>
                </div>
            </div>
        </div>
        <attachment-modal
            ref="atachmentModal"
            :conversation-id="conversation.id.toString()"
            @sendedAttachment="(message) => displayMessage(message)"
            @sendedAttachmentError="(error) => displayError(error)"
        >
        </attachment-modal>
    </div>
</template>
<script setup lang="ts">
import type { ConversationData } from "../Interfaces/ConversationData";
import type { MessageData } from "../Interfaces/MessageData";
import { Link } from "@inertiajs/inertia-vue";
import { Inertia } from '@inertiajs/inertia'
import UseChatDataConverting  from '../Helpers/useChatDataConverting'
import {ref, reactive, computed, watch, nextTick, onUpdated, onMounted} from 'vue'
import axios from 'axios';
import {AccountPhoneNumber} from "../Interfaces/AccountPhoneNumber";
import AttachmentModal from "../Components/AttachmentModal.vue";
import ViewAttachmentModal from "../Components/ViewAttachmentModal.vue";
import Message from "../Components/Message.vue";
import useKeyDown from "../Composables/use-keydown";

// Emojis
// Import data/twitter.json to reduce size, all.json contains data for
// all emoji sets.
import data from "emoji-mart-vue-fast/data/all.json";
// Import default CSS
import "emoji-mart-vue-fast/css/emoji-mart.css";
// Vue 2:
import { Picker, EmojiIndex, EmojiView } from "emoji-mart-vue-fast";
import {PhoneNumber} from "../Interfaces/PhoneNumber";

const props = defineProps<{
    conversation: ConversationData
}>()

const file = ref<HTMLInputElement>()
const sendingMessageError = ref<String>('')
const viewAttachmentModal = ref()
const pinnedMessages = ref<Array<MessageData>>([])
const atachmentModal = ref()
const selectedMode = ref<Boolean>(false)
const isPickerVisible = ref<Boolean>(false)
const selectedMessages = ref([])
const messageComponents = ref<Array<InstanceType<typeof Message>>>([])
const smsTextArea = ref<HTMLInputElement>()
const picker = ref<HTMLInputElement>()
const msgData = ref<String>('')
const emojiIndex = new EmojiIndex(data);

const { getAvatarColor, formatAMPM, getIconUrl, getMmsUrl } = UseChatDataConverting();

useKeyDown([
    {'key': 'Escape', 'fn': () => { isPickerVisible.value = false; }}, // Emits 'closeModal' event on Escape button
]);

interface PhoneNumbersData {
    value: number;
    text: string;
}

interface PhoneNumbersData {
    value: number;
    text: string;
};

const numberSelectedMessages = computed(() => {
    return selectedMessages.value.length
});

const getPinnedMessages = computed(() => {
    pinnedMessages.value = props.conversation.messages?.filter((message: MessageData) => message.is_pinned)
    return pinnedMessages.value
});

const emit = defineEmits(['updateConversationsOrdering'])

function setMessageComponents(id: number, component: InstanceType<typeof Message>) {
    messageComponents.value[id] = component;
}

function shortMessage(message: string) {
    return (message !== null && message.length > 50)
        ? message.substring(0, 50) + '...'
        : message;
}
function showEmoji(emoji: EmojiView) {
    if(smsTextArea.value === undefined) return false;

    let start = smsTextArea.value.selectionStart !== null ? smsTextArea.value.selectionStart : 0,
        end   = smsTextArea.value.selectionEnd !== null ? smsTextArea.value.selectionEnd : 0,
        before = msgData.value.substring(0, start),
        after  = msgData.value.substring(end, msgData.value.length);
    msgData.value = before + emoji.native + after;
    smsTextArea.value.focus();

    nextTick(() => {
        if (smsTextArea.value !== undefined) {
            smsTextArea.value.selectionStart = smsTextArea.value.selectionEnd = start + emoji.native.length
        }
    });
}

function handleFileUpload() {
    let attachment = (file.value?.files?.length !== undefined && file.value?.files?.length > 0) ? file.value?.files[0] : undefined;
    atachmentModal.value.show(attachment);
    if(file.value !== undefined) {
        file.value.value = '';
    }
}

function inputHandler(e: KeyboardEvent) {
    if (e.key === "Enter" && e.shiftKey) {
        e.preventDefault();
        if(msgData.value !== '') {
            sendMessage();
        }
    }
}

function sendMessage(){
    axios.post('/sms-communications/messages', {
        body: msgData.value,
        conversation_id: props.conversation.id,
        channel: 'sms'
    })
    .then((response) => {
        displayMessage(response.data)
    })
    .catch(function (error) {
        if (error.response) {
            sendingMessageError.value = error.response.data.message
        } else {
            sendingMessageError.value = 'No response was received from the server';
        }
    });
}
function displayMessage(message: MessageData) {
    sendingMessageError.value = '';
    props.conversation.messages.push({
        id: message.id,
        conversation_id: props.conversation.id,
        message_type: message.message_type,
        file_name: message.file_name,
        is_incoming: message.is_incoming,
        is_unread: message.is_unread,
        is_pinned: message.is_pinned,
        status: message.status,
        service_message_id: message.service_message_id,
        body: message.body,
        user_id: message.user_id,
        created_at: message.created_at,
        updated_at: message.updated_at,
        user: message.user,
    });
    msgData.value = '';
    scrollToBottom();
    emit('updateConversationsOrdering', props.conversation.id);
}

function displayError(error: string) {
    sendingMessageError.value = error
}

function openViewAttachmentModal(message: MessageData) {
    viewAttachmentModal.value.show(message);
}

function deleteMessages() {
    axios.post('/sms-communications/messages/delete', {
        ids: selectedMessages.value
    })
    .then((response) => {
        Inertia.reload();
    })
    .catch(function (error) {
        if (error.response) {
            sendingMessageError.value = error.response.data.message
        } else {
            sendingMessageError.value = 'No response was received from the server';
        }
    });
}

function unreadMessages() {
    axios.post('/sms-communications/messages/unread', {
        ids: selectedMessages.value
    })
    .then((response) => {
        Inertia.visit('/sms-communications/conversations');
    })
    .catch(function (error) {
        if (error.response) {
            sendingMessageError.value = error.response.data.message
        } else {
            sendingMessageError.value = 'No response was received from the server';
        }
    });
}

function pinMessages() {
    axios.post('/sms-communications/messages/pin', {
        ids: selectedMessages.value
    })
    .then((response) => {
        Inertia.reload();
        unSelectedMode();
    })
    .catch(function (error) {
        if (error.response) {
            sendingMessageError.value = error.response.data.message
        } else {
            sendingMessageError.value = 'No response was received from the server';
        }
    });
}
function excludeMessageId(id: number) {
    selectedMessages.value = selectedMessages.value.filter((element: number) => {
        return element !== id
    })
}

function unSelectedMode() {
    selectedMode.value = false;
    resetSelected();
}

function resetSelected() {
    for (const index in messageComponents.value) {
        messageComponents.value[index].unselect();
    }
}

function blockContact() {
    axios.put(`/sms-communications/contacts/${props.conversation.phone_number.id}`)
    .then((response) => {
        props.conversation.phone_number.blocked_at = response.data ? response.data : null;
    })
    .catch(function (error) {
        if (error.response) {
            sendingMessageError.value = error.response.data.message
        } else {
            sendingMessageError.value = 'No response was received from the server';
        }
    });
}

function unpinAllMessages() {
    let ids = pinnedMessages.value.map(function(message: MessageData) {
        return message.id;
    });
    axios.post('/sms-communications/messages/unpin', {
        ids: ids
    })
    .then((response) => {
        Inertia.reload();
        unSelectedMode();
    })
    .catch(function (error) {
        if (error.response) {
            sendingMessageError.value = error.response.data.message
        } else {
            sendingMessageError.value = 'No response was received from the server';
        }
    });
}

function unpinMessage(id: number) {
    axios.post('/sms-communications/messages/unpin', {
        ids: [id]
    })
    .then((response) => {
        Inertia.reload();
        unSelectedMode();
    })
    .catch(function (error) {
        if (error.response) {
            sendingMessageError.value = error.response.data.message
        } else {
            sendingMessageError.value = 'No response was received from the server';
        }
    });
}

watch(() => props.conversation, (newVal, oldVal) => {
    if (newVal?.id === oldVal?.id) {
        return;
    }
    setTimeout(() => {
        scrollToBottom()
    }, 0);
}, { immediate: true});

function scrollToMessage(id: number) {
    messageComponents.value[id].scrollToMessage();
}
const scrollToBottom = function ()  {
    nextTick(() => {
        let element = document.getElementById('correspondence');
        if(!element) return;
        element.scrollTop = element.scrollHeight;
    });
}

defineExpose({scrollToBottom});

</script>

<style scoped>
    .input_msg_write textarea {
        border: medium none;
        color: #4c4c4c;
        font-size: 21px;
        min-height: 48px;
        width: 100%;
        padding-bottom: 75px;
    }
    .msg_send_btn {
        width: 120px;
        height: 45px;
        border: none;
        border-radius: 5px;
    }
    .msg_send_btn:disabled {
        background-color: #807e7e !important;
    }
    .custom-select {
        max-width: 260px;
    }
    .correspondence {
        max-height: calc(100% - 348px);
        overflow-y: auto;
    }
    .type_msg {
        z-index: 1000;
    }
    .custom_sms_attach {
        background-image: url('/vendor/sms-communications/images/icons/attachments.png');
        width: 30px;
        height: 30px;
        background-size: 30px 30px;
        cursor: pointer;
    }
    .sms_attach {
        width: 0.1px;
        height: 0.1px;
        opacity: 0;
        overflow: hidden;
        position: absolute;
        z-index: -1;
    }
    .msgButton {
        border: none;
        background: none;
        cursor: pointer;
        background-size: 20px 20px;
        background-repeat: no-repeat;
        padding-left: 25px;
        background-position-y: 4px;
        margin-right: 15px;
    }
    .deleteButton {
        color: red;
        background-image: url('/vendor/sms-communications/images/icons/delete.png');
    }
    .deleteButton:disabled,
    .deleteButton[disabled] {
        color: #d3928f;
    }

    .unreadButton {
        color: #164494FF;
        background-image: url('/vendor/sms-communications/images/icons/unread.png');
    }
    .unreadButton:disabled,
    .unreadButton[disabled] {
        color: #7691C2FF;
    }

    .pinButton {
        color: black;
        background-image: url('/vendor/sms-communications/images/icons/pin.png');
    }
    .pinButton:disabled,
    .pinButton[disabled] {
        color: #807E7EFF;
    }

    .pinnedMessagesBlock {
        width: 470px;
        height: 48px;
        cursor: pointer;
    }
    .pinnedMessagesBlock .message:hover {
        background-color: #EAE7E7FF;
    }
    .unpinnedAll {
        background: none;
        border: none;
    }
    .pinnedMessagesBlock .unpinned {
        background-image: url('/vendor/sms-communications/images/icons/unpin.png');
        border: none;
        background-size: 20px 20px;
        background-repeat: no-repeat;
        width: 20px;
        height: 20px;
        margin-right: 25px;
        align-self: center;
    }
</style>
