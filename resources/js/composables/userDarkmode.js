import { ref, watchEffect } from 'vue'

const isDark = ref(getInitialTheme())

function getInitialTheme() {
    // Si el usuario eligió manualmente
    const stored = localStorage.getItem('theme')
    if (stored === 'dark') return true
    if (stored === 'light') return false

    // Si no eligió, usar preferencia de hora
    const hour = new Date().getHours()
    return hour >= 19 || hour < 6 // entre 7pm y 6am -> modo oscuro
}

// Sincroniza el estado con la clase 'dark' en <html>
watchEffect(() => {
    const html = document.documentElement
    if (isDark.value) {
        html.classList.add('dark')
        localStorage.setItem('theme', 'dark')
    } else {
        html.classList.remove('dark')
        localStorage.setItem('theme', 'light')
    }
})

export function useDarkMode() {
    return { isDark }
}
