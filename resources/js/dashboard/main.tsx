import { createRoot } from 'react-dom/client'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import Dashboard from './pages/Dashboard'

const queryClient = new QueryClient({
    defaultOptions: {
        queries: {
            refetchOnWindowFocus: true,
            staleTime: 30_000,
        },
    },
})

const container = document.getElementById('app')
if (!container) {
    throw new Error('Root element #app not found')
}

createRoot(container).render(
    <QueryClientProvider client={queryClient}>
        <Dashboard />
    </QueryClientProvider>
)