<template>
    <div ref="messageWrap">
        <div class="position-relative d-flex justify-content-center border-top" v-if="index==0 || isDifDate(message, index)">
            <span class="msg_date position-absolute translate-middle px-3 bg-light py-2 px-2">{{getMessageDate(message)}}</span>
        </div>
        <div class="d-flex" :class="{'justify-content-end': ! message.is_incoming && !selectedMode, 'justify-content-between': ! message.is_incoming && selectedMode }">
            <label v-if="selectedMode" class="checbox-container mr-5 mb-5">
                <input type="checkbox" v-model="checked" :class="{'checked': selectedMessages.includes(message.id)}" />
                <span class="checkmark"></span>
            </label>
            <div v-if="message.is_incoming" class="py-3 px-3">
                <div class="d-flex flex-row mb-2">
                    <div>
                        <div class="avatar rounded-circle d-flex me-3 shadow-1-strong white-text mr-3 mb-2 bg-primary" style="width: 60px; height: 60px">
                            <span class="text-center align-self-center w-100">
                                <template v-if="conversation.phone_number.contact">
                                    {{getContactFirstLetters(conversation.phone_number.contact.name)}}
                                </template>
                                <template v-else>
                                    C
                                </template>
                            </span>
                        </div>
                        <div class="text-muted mr-3">{{formatAMPM(new Date(message.created_at))}}</div>
                    </div>
                    <div>
                        <div class="contact_name pt-3">
                            <template v-if="conversation.phone_number.contact">
                                {{conversation.phone_number.contact.name}}
                            </template>
                            <template v-else>
                                {{conversation.phone_number.value}}
                            </template>
                        </div>
                        <div class="incomming p-4">
                            <template v-if="message.message_type == 'text'">
                                <div class="msg_text bg-light">
                                    {{message.body}}
                                </div>
                            </template>
                            <template v-else-if="message.message_type == 'image'">
                                <a @click="openViewAttachModal(message)"><img :src="getMmsUrl(message.file_name)" width="300" /></a>
                            </template>
                            <template v-else-if="message.message_type == 'video'"></template>
                            <template v-else-if="message.message_type == 'audio'"></template>
                            <template v-else></template>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="d-flex flex-row-reverse mb-2 py-3 px-5">
                <div class="position-relative">
                    <div class="avatar rounded-circle d-flex me-3 shadow-1-strong white-text ml-4 mb-2 bg-warning" style="width: 60px; height: 60px">
                            <span class="text-center align-self-center w-100" >
                                {{getContactFirstLetters(message.user.name)}}
                            </span>
                    </div>
                    <div class="text-muted text-right">{{formatAMPM(new Date(message.created_at))}}</div>
                    <template v-if="props.conversation.account_phone_number.account.name == 'whatsapp'">
                        <img v-if="message.status === 'sent'" :src="getIconUrl('sent.jpg')" width="20" class="msg_tick_status">
                        <img v-else-if="message.status === 'delivered'" :src="getIconUrl('delivered.jpg')" width="20" class="msg_tick_status">
                        <img v-else-if="message.status === 'read'" :src="getIconUrl('read.jpg')" width="20" class="msg_tick_status">
                        <img v-else-if="message.status === 'failed'" :src="getIconUrl('failed.png')" width="20" class="msg_tick_status">
                    </template>
                </div>
                <div>
                    <div class="contact_name pt-3 text-right">{{message.user.name}}</div>
                    <div class="p-4">
                        <template v-if="message.message_type == 'text'">
                            <div class="msg_text bg-light">
                                {{message.body}}
                            </div>
                        </template>
                        <template v-else-if="message.message_type == 'image'">
                            <a @click="openViewAttachModal(message)"><img :src="getMmsUrl(message.file_name)" width="300" /></a>
                        </template>
                        <template v-else-if="message.message_type == 'video'">
                            <video width="300" height="240" controls>
                                <source :src="getMmsUrl(message.file_name)">
                            </video>
                        </template>
                        <template v-else-if="message.message_type == 'audio'">
                            <audio controls>
                                <source :src="getMmsUrl(message.file_name)" >
                            </audio>
                        </template>
                        <template v-else-if="message.message_type == 'document'">
                            File: <a :href="getMmsUrl(message.file_name)">{{message.file_name}}</a>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import {ref, reactive, computed, watch, defineEmits} from 'vue'
import type { PhoneNumber } from "../Interfaces/PhoneNumber";
import {AccountPhoneNumber} from "../Interfaces/AccountPhoneNumber";
import type { Account } from "../Interfaces/Account";
import UseChatDataConverting  from '../Helpers/useChatDataConverting'
import axios from "axios";
import { Inertia } from '@inertiajs/inertia'
import type { MessageData } from "../Interfaces/MessageData";
import {ConversationData} from "../Interfaces/ConversationData";


const { getAvatarColor, getContactFirstLetters, formatAMPM, getIconUrl, getMmsUrl } = UseChatDataConverting();

const accountPhoneNumber = ref<AccountPhoneNumber>()
const messageWrap = ref<HTMLDivElement>()
const phoneNumber = ref<PhoneNumber>()
const checked = ref<Boolean>(false)
const emit = defineEmits(['openViewAttachmentModal', 'selectedMessageId', 'removedMessageId'])

const props = defineProps<{
    message: MessageData,
    index: number,
    conversation: ConversationData,
    selectedMode: boolean
    selectedMessages: number[]
}>()

watch(checked, ( newValue, oldValue ) => {
    if(newValue) {
        emit('selectedMessageId', props.message.id)
    } else {
        emit('removedMessageId', props.message.id)
    }
})

const unselect = function ()  {
    checked.value = false;
}
const scrollToMessage = function ()  {
    if (messageWrap.value !== undefined) {
        let top = messageWrap.value.offsetTop;
        messageWrap.value.scrollIntoView({behavior: "smooth", block: "center", inline: "center"});
        messageWrap.value?.classList.remove("bg-none");
        messageWrap.value.classList.add("transitionBg");
        setTimeout(function() {
            messageWrap.value?.classList.remove("transitionBg");
            messageWrap.value?.classList.add("bg-none");
        }, 2000);
    }
}
defineExpose({unselect, scrollToMessage });

function isDifDate(message: MessageData, index: number): boolean {
    const prevMsgDate = new Date(props.conversation.messages[index-1].created_at),
        currentMsgDate = new Date(message.created_at);
    prevMsgDate.setHours(0,0,0,0)
    currentMsgDate.setHours(0,0,0,0)
    return (prevMsgDate.valueOf() === currentMsgDate.valueOf()) ? false : true;
}

function getMessageDate(message: MessageData): string {
    const date = new Date(message.created_at),
        weekDay = date.toLocaleString('default', {weekday: 'long'}),
        month = date.toLocaleString('default', { month: 'long' }),
        day = date.getDate(),
        year = date.getFullYear();

    return weekDay + ', ' + month + ' ' + day + 'th ' + year;
}

function openViewAttachModal(message: MessageData) {
    emit('openViewAttachmentModal', message)
}

</script>
<style scoped>
/* Customize the label (the container) */
.checbox-container {
    display: block;
    position: relative;
    padding-left: 35px;
    margin-bottom: 12px;
    cursor: pointer;
    font-size: 22px;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

/* Hide the browser's default checkbox */
.checbox-container input {
    position: absolute;
    bottom: 0;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
}

/* Create a custom checkbox */
.checkmark {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 35px;
    width: 35px;
    background-color: #eee;
    border-radius: 15px;
}

/* On mouse-over, add a grey background color */
.checbox-container:hover input ~ .checkmark {
    background-color: #ccc;
}

/* When the checkbox is checked, add a blue background */
.checbox-container input.checked ~ .checkmark {
    background-color: #00c851;
}

/* Create the checkmark/indicator (hidden when not checked) */
.checkmark:after {
    content: "";
    position: absolute;
    display: none;
}

/* Show the checkmark when checked */
.checbox-container input.checked ~ .checkmark:after {
    display: block;
}

/* Style the checkmark/indicator */
.checbox-container .checkmark:after {
    left: 13px;
    top: 9px;
    width: 6px;
    height: 15px;
    border: solid white;
    border-width: 0 3px 3px 0;
    -webkit-transform: rotate(45deg);
    -ms-transform: rotate(45deg);
    transform: rotate(45deg);
}

.msg_date{
    top:-18px;
    border-radius: 25px;
}
.contact_name {
    margin-bottom: 28px;
}
.msg_text {
    border-radius: 15px 0 15px 15px;
    max-width: 550px;
    min-width: 70px;
    padding: 8px 15px;
}
.incomming .msg_text {
    border-radius: 0 15px 15px 15px;
}
.transitionBg{
    background-color: #D9E0ECFF;
}
.bg-none {
    transition-duration: 1s;
    transition-property: background-color;
    background-color: #fff;
}
.msg_tick_status {
    position: absolute;
    bottom: 25px;
    right: 3px;
}
</style>
