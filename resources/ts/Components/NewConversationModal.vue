<template>
    <mdb-modal :show="active" @close="hide">
        <mdb-modal-header>
            <mdb-modal-title>
                Start new Conversation
                <div if="errorMessage" class="text-danger">{{errorMessage}}</div>
            </mdb-modal-title>
        </mdb-modal-header>
        <mdb-modal-body class="modal_body">
            <div class="mb-5">
                <label><b>Service phone number</b></label>
                <multiselect
                    v-model="accountPhoneNumber"
                    :options="accountPhoneNumbersGrouped"
                    track-by="id"
                    placeholder="Select service phone number"
                    select-label=""
                    deselect-label=""
                    :customLabel="accountPhoneNumberRow"
                    group-values="phone_numbers"
                    group-label="service"
                >
                    <template #option="optionProps">
                        <template v-if="optionProps.option.$groupLabel">
                            <div>
                                <span class="mx-1 ml-2">{{optionProps.option.$groupLabel.charAt(0).toUpperCase()}}{{optionProps.option.$groupLabel.slice(1)}}</span>
                            </div>
                        </template>
                        <template v-else>
                            <div>
                                <img :src="getIconUrl(optionProps.option.account?.name+'.png')" width="30" height="30">
                                <span class="pt-1" style="font-size: 18px">{{optionProps.option.value}}</span>
                            </div>
                        </template>
                    </template>
                </multiselect>
            </div>
            <label><strong>Select contact</strong></label>
            <multiselect
                v-model="phoneNumberData"
                track-by="id"
                placeholder="Select contact"
                :options="contactsGrouped"
                :searchable="true"
                :allow-empty="false"
                :customLabel="contactRow"
                select-label=""
                deselect-label=""
                group-values="phone_numbers"
                group-label="contact"
            >
                <template #option="optionProps">
                    <template v-if="optionProps.option.$isLabel">
                        <template v-if="optionProps.option.$groupLabel">
                            <div class="d-flex flex-row justify-content-between align-items-center" style="font-size: 22px">
                                <div class="d-flex align-items-center">
                                    <div class="avatar rounded-circle me-3 shadow-1-strong mr-3 d-flex" style="width: 50px; height: 50px; color: white" :class="getAvatarColor()">
                                        <span class="text-center align-self-center w-100" style="font-size: 14px">
                                            <template>
                                                {{getContactFirstLetters(optionProps.option.$groupLabel)}}
                                            </template>
                                        </span>
                                    </div>
                                    <div>
                                        <span v-if="optionProps.option.$groupLabel" class="fw-bold mb-0">{{ optionProps.option.$groupLabel }}</span>
                                        <span v-else>Contact</span>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <div style="font-size: 22px" class="py-3">Phone numbers without contact</div>
                        </template>
                    </template>
                    <template v-else>
                        <div class="d-flex flex-row justify-content-between my-3">
                            <div>
                                <template v-if="optionProps.option.is_landline">
                                    Office
                                </template>
                                <template v-else>
                                    Mobile
                                </template>
                            </div>
                            <div class="small mb-0 text-muted" style="font-size: 23px"><span>{{ optionProps.option.value }}</span></div>
                        </div>
                    </template>
                </template>
            </multiselect>
            <div class="text-center mt-4 mb-3 block"><strong>OR</strong></div>
            <div class="form-group">
                <label for="example1">Add new</label>
                <input v-model="phoneNumber" type="text" placeholder="Phone number" id="example1" class="form-control">
            </div>
        </mdb-modal-body>
        <mdb-modal-footer>
            <mdb-btn @click="startConversation" color="primary">Start Conversation</mdb-btn>
        </mdb-modal-footer>
    </mdb-modal>
</template>

<script setup lang="ts">
import {ref, reactive, computed, watch} from 'vue'
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
import {useModal} from "../Composables/modals";
import {MessageData} from "../Interfaces/MessageData";

const { getAvatarColor, getContactFirstLetters, formatAMPM, getIconUrl } = UseChatDataConverting();

const accountPhoneNumber = ref<AccountPhoneNumber>()
const phoneNumberData = ref<PhoneNumber>()
const phoneNumber = ref<String>('')
const errorMessage = ref<String>('')
const contacts = ref<Array<PhoneNumber>>([])

const props = defineProps<{
    accountPhoneNumbers: Array<AccountPhoneNumber>
}>()

const { active, show, hide } = useModal({
    onShow: (value: Array<PhoneNumber>) => {
        contacts.value = value;
    },
    onHide: () => {
        contacts.value = [];
    },
});
defineExpose({ show, hide });

const accountPhoneNumbersGrouped = computed(() => {

    interface GroupingOptions {
        service: string
        phone_numbers: AccountPhoneNumber[]
    }

    let result: GroupingOptions[] = [],
        desiredObj: GroupingOptions | undefined;

    props.accountPhoneNumbers.map((element, index) => {
        if(element.account === undefined) return false;

        desiredObj = result.find(obj => obj.service === element.account?.name)
        if (desiredObj !== undefined) {
            desiredObj['phone_numbers'].push(element)
        } else {
            result.push({
                service: element.account?.name,
                phone_numbers: [element]
            })
        }
    });

    return result
});

const contactsGrouped = computed(() => {
    interface GroupingOptions {
        contact_id: number|null
        contact: string|undefined
        phone_numbers: PhoneNumber[]
    }
    let result: GroupingOptions[] = [],
        desiredObj: GroupingOptions | undefined;
    contacts.value.map((element, index) => {
        desiredObj = result.find(obj => obj.contact_id === element.contact_id)
        if (desiredObj !== undefined) {
            desiredObj['phone_numbers'].push(element)
        } else {
            result.push({
                contact_id: element.contact_id,
                contact: element.contact?.name,
                phone_numbers: [element]
            })
        }
    });
console.log(result)
    return result
});


function contactRow (contact: PhoneNumber) {
    return contact.contact !== null ? `${contact.contact?.name}: ${contact.value}` : `${contact.value}`;
}

function accountPhoneNumberRow (account_phone_number: AccountPhoneNumber) {
    return `${account_phone_number.account?.name.charAt(0).toUpperCase()}${account_phone_number.account?.name.slice(1)}: ${account_phone_number.value}`
}

function startConversation() {
    axios.post('/sms-communications/conversations', {
        account_phone_number_id: accountPhoneNumber.value?.id,
        phone_number_id: phoneNumberData.value?.id,
        phone_number: phoneNumber.value ? phoneNumber.value : undefined
    })
    .then((response) => {
        Inertia.visit(`/sms-communications/conversations/${response.data.id}`)
    })
    .catch(function (error) {
        if (error.response) {
            errorMessage.value = error.response.data.message;
        } else {
            errorMessage.value = 'No response was received from the server';
        }
    });
}

</script>
<style scoped>
.modal_body {
    min-height: 400px;
}
</style>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
