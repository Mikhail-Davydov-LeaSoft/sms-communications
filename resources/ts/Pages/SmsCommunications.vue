<template>
    <mdb-container class="pt-3 h-100" fluid>
        <mdb-row class="h-100">
            <mdb-col class="left-chat-column col-md-4 col-lg-4 col-xl-4 border-right fixed-top h-100 mt-3">
                <div class="d-flex">
                    <input v-model="search" class="form-control mr-1" type="text" placeholder="Search" aria-label="Search" />
                    <select class="browser-default custom-select mr-2" v-model="filter">
                        <option :value="filter" v-for="filter in filters">{{filter}}</option>
                    </select>
                    <a @click="getContacts">
                        <img class="mt-1" :src="getIconUrl('new_conversation.png')" width="30" height="30">
                    </a>
                    <new-conversation-modal
                        ref="newConversationModal"
                        :account-phone-numbers="accountPhoneNumbers"
                    >
                    </new-conversation-modal>
                </div>
                <ul class="list-unstyled mb-0 mt-2">
                    <conversation-card
                        v-for="(conversation, index) in filteredConversations"
                        :key="index"
                        :conversation="conversation"
                    ></conversation-card>
                </ul>
            </mdb-col>
            <mdb-col class="right-chat-column col-md-8 col-lg-8 col-xl-8 offset-4 h-100">
                <conversation-messages
                    ref="conversationMessagesComponent"
                    v-if="activeConversation"
                    :conversation="activeConversation"
                    @updateConversationsOrdering="(conversationId) => updateConversationsOrdering(conversationId)"
                ></conversation-messages>
                <h3 v-else class="text-center mt-4 text-muted ">Select a conversation on the left to send a message</h3>
            </mdb-col>
        </mdb-row>
    </mdb-container>
</template>
<script setup lang="ts">
import type { ConversationData } from "../Interfaces/ConversationData";
import type { PhoneNumber } from "../Interfaces/PhoneNumber";
import {ref, onMounted, reactive, computed, nextTick} from 'vue'
import mdbContainer from "mdbvue/lib/components/mdbContainer";
import mdbRow from "mdbvue/lib/components/mdbRow";
import mdbCol from "mdbvue/lib/components/mdbCol";
import NewConversationModal from "../Components/NewConversationModal.vue";
import ConversationCard from "../Components/ConversationCard.vue";
import ConversationMessages from "../Components/ConversationMessages.vue";
import UseChatDataConverting  from '../Helpers/useChatDataConverting'
import axios from "axios";
import {Account} from "../Interfaces/Account";
import {AccountPhoneNumber} from "../Interfaces/AccountPhoneNumber";

interface Props {
    conversations: Array<ConversationData>
    activeConversation?: ConversationData
    accountPhoneNumbers: Array<AccountPhoneNumber>
}
const props = defineProps<Props>()
const search = ref('')
const filter = ref('all')
const updatedConversationId = ref()
const newConversationModal = ref()
const { getIconUrl } = UseChatDataConverting();
const conversationMessagesComponent = ref()


const filters = ['all', 'unread']

const filteredConversations = computed(() => {
    orderByLastMesssage()
    if(search.value == '' && filter.value == 'all') return props.conversations;
    return props.conversations.filter(conversation => {
        if(filter.value === 'unread' && search.value !== '') {
            return conversation.messages.filter(message => message.is_unread).length > 0 &&
                (conversation.phone_number.value.toLowerCase().includes(search.value.toLowerCase()) ||
                    conversation.phone_number.contact?.name.toLowerCase().includes(search.value.toLowerCase()))
        }
        if(filter.value === 'unread') {
            return conversation.messages.filter(message => message.is_unread).length > 0;
        }
        if(search.value !== '') {
            return conversation.phone_number.value.toLowerCase().includes(search.value.toLowerCase()) ||
                conversation.phone_number.contact?.name.toLowerCase().includes(search.value.toLowerCase());
        }
    })
});

onMounted( () => {
    conversationMessagesComponent.value.scrollToBottom();
});

function updateConversationsOrdering(id = null) {
    updatedConversationId.value = id
    filteredConversations;
}
function orderByLastMesssage() {
    return props.conversations.sort((a, b) => {

        if(a.messages.length == 0 && b.messages.length == 0) return 0;
        if(a.messages.length == 0) return 1;
        if(b.messages.length == 0) return -1;

        if(updatedConversationId.value && (a.id == updatedConversationId.value || b.id == updatedConversationId.value)) return -1;

        let aDate = new Date(a.messages[a.messages.length - 1]?.created_at)
        let bDate = new Date(b.messages[b.messages.length - 1]?.created_at)

        return  bDate.getTime() - aDate.getTime()
    })
}
function getContacts() {
    axios.get('/sms-communications/contacts')
    .then((response) => {
        newConversationModal.value.show(response.data)
    })
    .catch(function (error) {
        newConversationModal.value.show(null)
    });
}
</script>

<style scoped>
.custom-select {
    width: 100px;
}
.left-chat-column {
    overflow-x: auto;
}
.right-chat-column {
    overflow-x: auto;
}
</style>
