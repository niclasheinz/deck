<!--
  - SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<CardDetailEntry :label="t('deck', 'Set start date for this card…')" data-test="start-date-selector">
		<CalendarStart slot="icon" :size="20" />
		<template v-if="!card.done && !card.archived">
			<div class="date-input-wrapper">
				<label for="card-startdate-picker">{{ t('deck', 'Start date') }}</label>
				<NcDateTimePickerNative 
					id="card-startdate-picker"
					v-model="startdate"
					:placeholder="t('deck', 'Set start date')"
					:hide-label="true"
					type="datetime-local" />
			</div>
			<NcActions v-if="canEdit"
				:menu-title="t('deck', 'Start date shortcuts')"
				type="tertiary"
				data-cy-date-actions>
				<template #icon>
					<Plus :size="20" />
				</template>
				<NcActionButton v-for="shortcut in startDateOptions"
					:key="shortcut.key"
					close-after-click
					:data-cy-start-shortcut="shortcut.key"
					@click="() => selectStartShortcut(shortcut)">
					{{ shortcut.label }}
				</NcActionButton>
				<NcActionSeparator />
				<NcActionButton 
					icon="icon-delete"
					close-after-click
					data-cy-start-remove
					@click="clearStartDate">
					{{ t('deck', 'Clear start date') }}
				</NcActionButton>
			</NcActions>
		</template>
		<template v-else>
			<div class="date-info">
				<span v-if="startdate" class="date-info--start">
					{{ t('deck', 'Started:') }} {{ formatReadableDate(startdate) }}
				</span>
			</div>
		</template>
	</CardDetailEntry>
</template>

<script>
import { defineComponent } from 'vue'
import {
	NcActionButton,
	NcActions,
	NcActionSeparator,
	NcDateTimePickerNative,
} from '@nextcloud/vue'
import readableDate from '../../mixins/readableDate.js'
import moment from '@nextcloud/moment'
import Plus from 'vue-material-design-icons/Plus.vue'
import CalendarStart from 'vue-material-design-icons/CalendarStart.vue'
import CardDetailEntry from './CardDetailEntry.vue'

export default defineComponent({
	name: 'StartDateSelector',
	components: {
		CardDetailEntry,
		Plus,
		CalendarStart,
		NcActions,
		NcActionButton,
		NcActionSeparator,
		NcDateTimePickerNative,
	},
	mixins: [
		readableDate,
	],
	props: {
		card: {
			type: Object,
			default: null,
		},
		canEdit: {
			type: Boolean,
			default: false,
		},
	},
	computed: {
		startdate: {
			get() {
				return this.card?.startdate ? new Date(this.card.startdate) : null
			},
			set(val) {
				const newCard = { ...this.card, startdate: val }
				this.$store.dispatch('updateCardStart', newCard)
				this.$emit('change', { field: 'startdate', value: val })
			},
		},
		startDateOptions() {
			const now = moment()
			return [
				{
					key: 'now',
					label: t('deck', 'Now'),
					startTime: now.clone(),
				},
				{
					key: 'tomorrow',
					label: t('deck', 'Tomorrow morning'),
					startTime: now.clone().add(1, 'day').hour(9).minute(0),
				},
				{
					key: 'nextWeek',
					label: t('deck', 'Next week'),
					startTime: now.clone().add(1, 'week').hour(9).minute(0),
				},
				{
					key: 'nextMonth',
					label: t('deck', 'Next month'),
					startTime: now.clone().add(1, 'month').hour(9).minute(0),
				},
			]
		},
	},
	methods: {
		selectStartShortcut(shortcut) {
			const startTime = shortcut.startTime.toDate()
			this.startdate = startTime
			this.$emit('change', { field: 'startdate', value: startTime })
		},
		clearStartDate() {
			this.startdate = null
			this.$emit('change', { field: 'startdate', value: null })
		},
	},
})
</script>

<style scoped lang="scss">
.date-input-wrapper {
	display: flex;
	flex-direction: column;
	gap: 4px;
	width: 100%;
}

.date-input-wrapper label {
	font-weight: 500;
	color: var(--color-text-maxcontrast);
	font-size: 13px;
}

.date-info {
	display: flex;
	flex-direction: column;
	gap: 4px;
}

.date-info--start {
	display: flex;
	color: var(--color-text-maxcontrast);
}
</style>