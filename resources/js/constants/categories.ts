export const CATEGORIES = {
    food: { id: 'food', label: 'Comida', icon: '🍔', color: 'bg-orange-500', text: 'text-orange-100' },
    drinks: { id: 'drinks', label: 'Bebidas', icon: '🍺', color: 'bg-amber-500', text: 'text-amber-100' },
    market: { id: 'market', label: 'Super', icon: '🛒', color: 'bg-emerald-500', text: 'text-emerald-100' },
    transport: { id: 'transport', label: 'Transporte', icon: '🚕', color: 'bg-blue-500', text: 'text-blue-100' },
    entertainment: { id: 'entertainment', label: 'Ocio', icon: '🎫', color: 'bg-purple-500', text: 'text-purple-100' },
    lodging: { id: 'lodging', label: 'Alojamiento', icon: '🏠', color: 'bg-indigo-500', text: 'text-indigo-100' },
    gifts: { id: 'gifts', label: 'Regalos', icon: '🎁', color: 'bg-pink-500', text: 'text-pink-100' },
    other: { id: 'other', label: 'Varios', icon: '📦', color: 'bg-slate-500', text: 'text-slate-100' },
} as const;

export type CategoryId = keyof typeof CATEGORIES;

export const getCategory = (id?: string) => {
    return CATEGORIES[(id as CategoryId) || 'other'];
};
