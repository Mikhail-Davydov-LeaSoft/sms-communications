export default function () {
    function getAvatarColor(): string {
        const colors = ['default-color', 'primary-color', 'secondary-color', 'warning-color-dark', 'success-color-dark', 'info-color-dark', 'unique-color', 'warning-color', 'success-color']
        return colors[Math.floor(Math.random() * colors.length)];
    }
    function getContactFirstLetters(name: string): string {
        let fullName = name.split(/\s+/);
        return fullName.length === 2 ? fullName[0].charAt(0).toUpperCase() + fullName[1].charAt(0).toUpperCase() : fullName[0].charAt(0).toUpperCase();
    }
    function formatAMPM(date: Date): string {
        let hours: number  = date.getHours(),
            minutes: number = date.getMinutes(),
            ampm: string = hours >= 12 ? 'pm' : 'am';
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        let minutesStr: string = minutes < 10 ? '0' + minutes.toString() : minutes.toString();
        return  hours.toString() + ':' + minutesStr + ' ' + ampm;
    }
    function getIconUrl(name: String) {
        return window.location.origin + '/vendor/sms-communications/images/icons/' + name;
    }
    function getMmsUrl(name: String) {
        return window.location.origin + '/storage/images/mms/' + name;
    }
    return {
        getAvatarColor,
        getContactFirstLetters,
        formatAMPM,
        getIconUrl,
        getMmsUrl
    }
}
