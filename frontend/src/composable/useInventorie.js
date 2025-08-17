// composables/useInventories.js
import axios from 'axios'
import Cookies from 'js-cookie'
import api from '../utils/api'

// Sử dụng instance axios chung từ utility
const API = api

export const useInventories = () => {
    const getInventories = async (params = {}) => {
        try {
            const res = await API.get('/api/inventory', { params })
            return res.data
        } catch (err) {
            console.error('Error fetching inventories:', err)
            throw err
        }
    }

    const createStockMovement = async (data) => {
        try {
            const res = await API.post('/api/stock-movement', data, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                    Accept: 'application/json'
                }
            })
            return res.data
        } catch (err) {
            console.error('Error creating stock movement:', err)
            throw err
        }
    }

    const getStockMovement = async () => {
        try {
            const res = await API.get('/api/stock-movement')
            return res.data
        } catch (err) {
            console.error('Error fetching stock movement:', err)
            return []
        }
    }

    return {
        getInventories,
        createStockMovement,
        getStockMovement
    }
}
