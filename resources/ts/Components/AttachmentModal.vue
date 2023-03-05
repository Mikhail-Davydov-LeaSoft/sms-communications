<template>
    <mdb-modal :show="active" @close="hide">
        <form id="new-file-form" @submit.prevent="sendAttachment()">
            <mdb-modal-header>
                <mdb-modal-title>Send MMS</mdb-modal-title>
            </mdb-modal-header>
            <mdb-modal-body class="modal_body">
                <strong class="font-weight-bold">Attachment file:</strong> {{ file?.name }}
            </mdb-modal-body>
            <mdb-modal-footer>
                <mdb-btn type="submit" color="primary">Send</mdb-btn>
            </mdb-modal-footer>
        </form>
    </mdb-modal>
</template>

<script setup lang="ts">
import {ref, reactive, computed, watch, defineEmits} from 'vue'
import type { PhoneNumber } from "../Interfaces/PhoneNumber";
import type { Account } from "../Interfaces/Account";
import { Link } from "@inertiajs/inertia-vue";
import UseChatDataConverting  from '../Helpers/useChatDataConverting'
import mdbModal from "mdbvue/lib/components/mdbModal";
import mdbModalBody from "mdbvue/lib/components/mdbModalBody";
import mdbModalFooter from "mdbvue/lib/components/mdbModalFooter";
import mdbModalHeader from "mdbvue/lib/components/mdbModalHeader";
import mdbBtn from "mdbvue/lib/components/mdbBtn";
import mdbModalTitle from "mdbvue/lib/components/mdbModalTitle";
import Multiselect from 'vue-multiselect'
import {AccountPhoneNumber} from "../Interfaces/AccountPhoneNumber";
import axios from "axios";
import { Inertia } from '@inertiajs/inertia'
import { useModal } from "../Composables/modals";
import {MessageData} from "../Interfaces/MessageData";

const { getAvatarColor, getContactFirstLetters, formatAMPM, getIconUrl } = UseChatDataConverting();

const accountPhoneNumber = ref<AccountPhoneNumber>()
const phoneNumber = ref<PhoneNumber>()
const file = ref<File>()
const emit = defineEmits(['closeModal', 'sendedAttachment', 'sendedAttachmentError'])

const props = defineProps<{
    conversationId: string
}>()

const { active, show, hide } = useModal({
    onShow: (value: File) => {
        file.value = value;
    },
    onHide: () => {
        file.value = undefined;
    },
});
defineExpose({ show, hide });

function sendAttachment() {
    let formData = new FormData()

    if(file.value !== undefined) {
        formData.append('file', file.value)
    }
    formData.append('conversation_id', props.conversationId)
    formData.append('channel', 'mms')

    axios.post('/sms-communications/messages', formData,
    {headers: {'content-type': 'multipart/form-data'}})
    .then((response) => {
        emit('sendedAttachment', response.data)
            hide()
    })
    .catch(function(error) {
        if (error.response) {
            emit('sendedAttachmentError', error.response.data.message)
        } else {
            emit('sendedAttachmentError', 'No response was received from the server')
        }
        hide()
    });
}


</script>
<style scoped>
.modal_body {

}
</style>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
