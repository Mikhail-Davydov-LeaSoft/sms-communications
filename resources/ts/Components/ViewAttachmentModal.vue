<template>
    <mdb-modal class="viewAttachmentModal" size="fluid" :show="active" @close="hide">
        <mdb-modal-header>
            <mdb-modal-title>{{message?.file_name}}</mdb-modal-title>
        </mdb-modal-header>
        <mdb-modal-body class="modal_body">
            <div class="text-center" v-if="message?.message_type == 'image'">
                <img :src="getMmsUrl(message?.file_name)" />
            </div>
        </mdb-modal-body>
        <mdb-modal-footer>
        </mdb-modal-footer>
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
import {MessageData} from "../Interfaces/MessageData";
import { useModal } from "../Composables/modals";

const { getMmsUrl } = UseChatDataConverting();
const emit = defineEmits(['closeModal'])
const message = ref<MessageData>()

const { active, show, hide } = useModal({
    onShow: (value: MessageData) => {
        message.value = value;
    },
    onHide: () => {
        message.value = undefined;
    },
});
defineExpose({ show, hide });

function closeModal() {
    emit('closeModal')
}

</script>
<style scoped>

</style>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
