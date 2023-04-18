<template>
    <li class="p-2 border-bottom border-top" :class="{ 'bg-primary': $page.url === `/sms-communications/conversations/${conversation.id}` }">
        <Link class="d-flex justify-content-between black-text"
              :href="'/sms-communications/conversations/'+conversation.id"
              preserve-state
        >
            <div class="d-flex flex-row">
                <div>
                    <div class="avatar rounded-circle d-flex me-3 shadow-1-strong white-text mr-5" style="width: 60px; height: 60px" :class="getAvatarColor()">
                        <span class="text-center align-self-center w-100">
                            <template v-if="conversation.phone_number.contact">
                                {{getContactFirstLetters(conversation.phone_number.contact.name)}}
                            </template>
                            <template v-else>
                                C
                            </template>
                        </span>
                    </div>
                    <div v-if="conversation.phone_number.blocked_at !== null" class="text-danger">Blocked</div>
                </div>
                <div class="pt-1">
                    <p v-if="conversation.phone_number.contact" class="fw-bold mb-0">{{ conversation.phone_number.contact.name }}</p>
                    <p class="small mb-0 text-muted">{{ conversation.phone_number.value }}</p>
                    <template v-if="props.conversation.messages.length > 0">
                        <template v-if="props.conversation.messages.slice(-1)[0].message_type == 'text'">
                            <p v-if="conversation.messages.length > 0" class="small truncatable">{{ lastTextMessage }}</p>
                        </template>
                        <template v-else-if="props.conversation.messages.slice(-1)[0].message_type == 'image'">
                            <span class="ml-2">Image: </span>
                            <img :src="getMmsUrl(props.conversation.messages.slice(-1)[0].file_name)" width="30px" height="30px" />
                        </template>
                        <template v-else-if="props.conversation.messages.slice(-1)[0].message_type == 'video'">
                            <span class="ml-2">Video:</span>
                            <p class="truncatable">{{props.conversation.messages.slice(-1)[0].file_name}}</p>
                        </template>
                        <template v-else-if="props.conversation.messages.slice(-1)[0].message_type == 'audio'">
                            <span class="ml-2">Audio:</span>
                            <p class="truncatable">{{props.conversation.messages.slice(-1)[0].file_name}}</p>
                        </template>
                        <template v-else>
                            <span class="ml-2">Document:</span>
                            <p class="truncatable">{{props.conversation.messages.slice(-1)[0].file_name}}</p>
                        </template>
                    </template>
                </div>
            </div>
            <div class="d-flex flex-row pt-1 text-primary">
                <img :src="getIconUrl(conversation.account_phone_number.account.name+'.png')" width="30" height="30">
                <span class="mx-1 pt-1 service_name" :class="conversation.account_phone_number.account.name">{{conversation.account_phone_number.account.name.toUpperCase()}}</span>
                <span class="pt-1">{{conversation.account_phone_number.value}}</span>
            </div>
            <div v-if="conversation.messages.length > 0" class="pt-1">
                <p class="small text-muted mb-3">{{ getLastMsgDate() }}</p>
                <span v-if="unreadCount" class="badge bg-danger rounded-pill float-right">{{unreadCount}}</span>
            </div>
        </Link>
    </li>
</template>
<script setup lang="ts">
import {ref, reactive, computed, watch, defineEmits} from 'vue'
import type { ConversationData } from "../Interfaces/ConversationData";
import type { MessageData } from "../Interfaces/MessageData";
import { Link } from "@inertiajs/inertia-vue";
import UseChatDataConverting  from '../Helpers/useChatDataConverting'

const props = defineProps<{
    conversation: ConversationData
}>()

const { getAvatarColor, getContactFirstLetters, formatAMPM, getIconUrl, getMmsUrl } = UseChatDataConverting();

const lastTextMessage = computed(() => {

    if (props.conversation.messages.length === 0) {
        return null;
    }

    const lastMessage = props.conversation.messages.slice(-1)[0];

    if (lastMessage === undefined) {
        return null;
    }

    return lastMessage.body;
});
const unreadCount = computed(() => {
    return props.conversation.messages.filter((message: MessageData) => message.is_unread && message.is_incoming).length;
});

function getLastMsgDate(): string {
    let msgDate: Date|null = new Date(props.conversation.messages.slice(-1)[0].created_at);

    if(isToday(msgDate)) {
        return formatAMPM(msgDate)
    }

    return msgDate.toLocaleString('default', { month: 'short' }) + ' ' + msgDate.getDate();
}

function isToday(date: Date): boolean {
    let today = new Date();

    if (today.toDateString() === date.toDateString()) {
        return true;
    }

    return false;
}

</script>

<style scoped>
li.bg-primary a p, li.bg-primary a span{
    color: #fff !important;
}
.service_name.nexmo{
    color: #000;
}
.service_name.brytecall{
    color: #cd6f15;
}
.service_name.whatsapp{
    color: #28a745;
}
.truncatable {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 138px;
}

</style>
