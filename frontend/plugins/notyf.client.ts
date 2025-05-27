import { Notyf } from 'notyf'
import 'notyf/notyf.min.css'

export default defineNuxtPlugin(() => {
    const notyf = new Notyf()
    return {
        provide: {
            notyf
        }
    }
})
