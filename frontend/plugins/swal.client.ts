import Swal from 'sweetalert2'
import 'sweetalert2/dist/sweetalert2.min.css'

export default defineNuxtPlugin(() => {
    return {
        provide: {
            swal: Swal
        }
    }
})
