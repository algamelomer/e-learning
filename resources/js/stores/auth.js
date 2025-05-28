import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: JSON.parse(localStorage.getItem('user') || 'null'),
        token: localStorage.getItem('token'),
        loading: false,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
        getUser: (state) => state.user,
        isAdmin: (state) => {
            if (!state.user || !state.user.roles) return false;
            return state.user.roles.some(role => role.name === 'admin');
        },
    },

    actions: {
        async login(credentials) {
            try {
                const response = await axios.post('/api/login', credentials);
                this.token = response.data.token;
                this.user = response.data.user;
                localStorage.setItem('token', this.token);
                localStorage.setItem('user', JSON.stringify(this.user));
                // Set the token in axios headers
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
                return response;
            } catch (error) {
                this.token = null;
                this.user = null;
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                throw error;
            }
        },

        async register(userData) {
            try {
                const response = await axios.post('/api/register', userData);
                this.token = response.data.token;
                this.user = response.data.user;
                localStorage.setItem('token', this.token);
                localStorage.setItem('user', JSON.stringify(this.user));
                // Set the token in axios headers
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
                return response;
            } catch (error) {
                this.token = null;
                this.user = null;
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                throw error;
            }
        },

        async logout() {
            try {
                if (this.token) {
                    await axios.post('/api/logout');
                }
            } catch (error) {
                console.error('Logout failed:', error);
            } finally {
                this.token = null;
                this.user = null;
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                delete axios.defaults.headers.common['Authorization'];
            }
        },

        async fetchUser() {
            try {
                if (!this.token) {
                    throw new Error('No token found');
                }
                const response = await axios.get('/api/user');
                this.user = response.data;
                localStorage.setItem('user', JSON.stringify(this.user));
                return response;
            } catch (error) {
                this.token = null;
                this.user = null;
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                throw error;
            }
        },
    },
});