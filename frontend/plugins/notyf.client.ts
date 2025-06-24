import { Notyf } from 'notyf'
import 'notyf/notyf.min.css'

export default defineNuxtPlugin(() => {
    const notyf = new Notyf({
        duration: 3000,
        position: {
            x: 'center',
            y: 'top',
        },
    })
    return {
        provide: {
            notyf
        }
    }
})
