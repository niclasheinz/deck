<template>
	<div class="date-selectors-container">
		<!-- Start Date oben -->
		<div class="date-input-section">
			<div class="selector-wrapper">
				<div class="selector-wrapper--icon">
					<CalendarStart :size="20" />
				</div>
				<div class="selector-wrapper--content">
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
						class="date-actions">
						<template #icon>
							<Plus :size="20" />
						</template>
						<NcActionButton v-for="shortcut in startDateOptions"
							:key="shortcut.key"
							close-after-click
							@click="() => selectStartShortcut(shortcut)">
							{{ shortcut.label }}
						</NcActionButton>
						<NcActionSeparator />
						<NcActionButton 
							icon="icon-delete"
							close-after-click
							@click="clearStartDate">
							{{ t('deck', 'Clear start date') }}
						</NcActionButton>
					</NcActions>
				</div>
			</div>
		</div>

		<!-- Due Date unten -->
		<div class="date-input-section">
			<div class="selector-wrapper">
				<div class="selector-wrapper--icon">
					<Calendar v-if="!card.done" :size="20" />
					<CalendarCheck v-else :size="20" />
				</div>
				<div class="selector-wrapper--content">
					<div class="date-input-wrapper">
						<label for="card-duedate-picker">{{ t('deck', 'Due date') }}</label>
						<NcDateTimePickerNative 
							id="card-duedate-picker"
							v-model="duedate"
							:placeholder="t('deck', 'Set a due date')"
							:hide-label="true"
							type="datetime-local" />
					</div>
					<NcActions v-if="canEdit"
						:menu-title="!duedate ? t('deck', 'Add due date') : null"
						type="tertiary"
						class="date-actions">
						<template v-if="!duedate" #icon>
							<Plus :size="20" />
						</template>
						<NcActionButton v-for="shortcut in reminderOptions"
							:key="shortcut.key"
							close-after-click
							@click="() => selectShortcut(shortcut)">
							{{ shortcut.label }}
						</NcActionButton>
						<NcActionSeparator />
						<NcActionButton v-if="!duedate"
							close-after-click
							@click="initDate">
							<template #icon>
								<Plus :size="20" />
							</template>
							{{ t('deck', 'Choose a date') }}
						</NcActionButton>
						<NcActionButton v-else
							icon="icon-delete"
							close-after-click
							@click="removeDue">
							{{ t('deck', 'Remove due date') }}
						</NcActionButton>
					</NcActions>
					
					<NcButton v-if="!card.done"
						type="secondary"
						class="completed-button"
						@click="changeCardDoneStatus()">
						<template #icon>
							<CheckIcon :size="20" />
						</template>
						{{ t('deck', 'Mark as done') }}
					</NcButton>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import { NcDateTimePickerNative, NcActions, NcActionButton, NcActionSeparator, NcButton } from '@nextcloud/vue'
import moment from '@nextcloud/moment'
import Plus from 'vue-material-design-icons/Plus.vue'
import CheckIcon from 'vue-material-design-icons/Check.vue'
import Calendar from 'vue-material-design-icons/Calendar.vue'
import CalendarCheck from 'vue-material-design-icons/CalendarCheck.vue'
import CalendarStart from 'vue-material-design-icons/CalendarStart.vue'

export default {
	name: 'DateSelectors',
	components: {
		NcDateTimePickerNative,
		NcActions,
		NcActionButton,
		NcActionSeparator,
		NcButton,
		Plus,
		CheckIcon,
		Calendar,
		CalendarCheck,
		CalendarStart,
	},
	props: {
		card: {
			type: Object,
			required: true,
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
				this.$emit('start-date-change', { field: 'startdate', value: val })
			},
		},
		duedate: {
			get() {
				return this.card?.duedate ? new Date(this.card.duedate) : null
			},
			set(val) {
				this.$emit('due-date-change', val ? new Date(val) : null)
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
			]
		},
		reminderOptions() {
			const currentDateTime = moment()
			const laterTodayTime = (currentDateTime.hour() < 18) ? moment().hour(18) : null
			const tomorrowTime = moment().add(1, 'days').hour(8)
			const thisWeekendTime = (currentDateTime.day() !== 6 && currentDateTime.day() !== 0) ? moment().day(6).hour(8) : null
			const nextWeekTime = moment().add(1, 'weeks').day(1).hour(8)
			
			return [
				{
					key: 'laterToday',
					timestamp: this.getTimestamp(laterTodayTime),
					label: t('deck', 'Later today – {timeLocale}', { timeLocale: laterTodayTime?.format('LT') }),
				},
				{
					key: 'tomorrow',
					timestamp: this.getTimestamp(tomorrowTime),
					label: t('deck', 'Tomorrow – {timeLocale}', { timeLocale: tomorrowTime?.format('ddd LT') }),
				},
				{
					key: 'thisWeekend',
					timestamp: this.getTimestamp(thisWeekendTime),
					label: t('deck', 'This weekend – {timeLocale}', { timeLocale: thisWeekendTime?.format('ddd LT') }),
				},
				{
					key: 'nextWeek',
					timestamp: this.getTimestamp(nextWeekTime),
					label: t('deck', 'Next week – {timeLocale}', { timeLocale: nextWeekTime?.format('ddd LT') }),
				},
			].filter(option => option.timestamp !== null)
		},
	},
	methods: {
		selectStartShortcut(shortcut) {
			const startTime = shortcut.startTime.toDate()
			this.startdate = startTime
		},
		clearStartDate() {
			this.startdate = null
		},
		initDate() {
			if (this.duedate === null) {
				const now = new Date()
				now.setDate(now.getDate() + 1)
				now.setHours(8)
				now.setMinutes(0)
				now.setMilliseconds(0)
				this.duedate = now
			}
		},
		removeDue() {
			this.duedate = null
		},
		selectShortcut(shortcut) {
			this.duedate = shortcut.timestamp
		},
		getTimestamp(momentObject) {
			return momentObject?.minute(0).second(0).millisecond(0).toDate() || null
		},
		changeCardDoneStatus() {
			this.$store.dispatch('changeCardDoneStatus', { ...this.card, done: !this.card.done })
		},
	},
}
</script>

<style scoped lang="scss">
.date-selectors-container {
	width: 100%;
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.date-input-section {
	width: 100%;
}

.selector-wrapper {
	display: flex;
	max-width: 100%;
	
	&--icon {
		width: var(--default-clickable-area);
		height: var(--default-clickable-area);
		flex-shrink: 0;
		display: flex;
		align-items: center;
		align-content: center;
		justify-content: center;
		color: var(--color-text-maxcontrast);
	}
	
	&--content {
		display: flex;
		flex-grow: 1;
		align-items: center;
		gap: 8px;
		min-width: 0;
	}
}

.date-input-wrapper {
	flex: 1;
	display: flex;
	flex-direction: column;
	gap: 4px;
	min-width: 0;
	
	label {
		font-weight: 500;
		color: var(--color-text-maxcontrast);
		font-size: 13px;
	}
}

.date-actions {
	flex-shrink: 0;
}

.completed-button {
	flex-shrink: 0;
}
</style>
