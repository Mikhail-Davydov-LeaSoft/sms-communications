<template>
    <mdb-container class="pt-3 h-100" fluid>
        <mdb-row class="h-100">
            <mdb-col class="col-md-3 col-lg-3 col-xl-3 border-right fixed-top h-100 mt-3">
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
            <mdb-col class="col-md-9 col-lg-9 col-xl-9 offset-3 h-100">
                <conversation-messages
                    v-if="activeConversation"
                    :conversation="activeConversation"
                ></conversation-messages>
                <h3 v-else class="text-center mt-4 text-muted ">Select a conversation on the left to send a message</h3>
            </mdb-col>
        </mdb-row>
    </mdb-container>
</template>
<script setup lang="ts">
import type { ConversationData } from "../Interfaces/ConversationData";
import type { PhoneNumber } from "../Interfaces/PhoneNumber";
import {ref, onMounted, reactive, computed} from 'vue'
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
const newConversationModal = ref()
const { getIconUrl } = UseChatDataConverting();

const filters = ['all', 'unread']

const filteredConversations = computed(() => {
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
</style>
