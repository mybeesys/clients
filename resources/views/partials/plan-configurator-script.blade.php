<script>
    window.planConfigurator = window.planConfigurator || function planConfigurator(catalog, labels, options = {}) {
        const initial = options.initial || {};
        return {
            catalog,
            labels,
            wirePath: options.wirePath || 'data.plan_config',
            period: initial.period || 'Month',
            modules: Array.isArray(initial.modules) ? [...initial.modules] : [],
            activeRecommendation: null,
            employees: initial.employees ?? catalog?.quotas?.find(q => q.key === 'employees')?.included ?? 5,
            establishments: initial.establishments ?? catalog?.quotas?.find(q => q.key === 'establishments')?.included ?? 1,
            screen_devices: initial.screen_devices ?? catalog?.quotas?.find(q => q.key === 'screen_devices')?.included ?? 1,
            coupon_input: initial.coupon_code || '',
            coupon_code: '',
            coupon_discount: 0,
            coupon_label: '',
            coupon_message: '',
            coupon_ok: false,
            coupon_loading: false,
            init() {
                this.syncToWire();
                this.$watch('modules', () => { this.refreshCouponDiscount(); this.syncToWire(); });
                this.$watch('employees', () => { this.refreshCouponDiscount(); this.syncToWire(); });
                this.$watch('establishments', () => { this.refreshCouponDiscount(); this.syncToWire(); });
                this.$watch('screen_devices', () => { this.refreshCouponDiscount(); this.syncToWire(); });
                this.$watch('period', () => { this.refreshCouponDiscount(); this.syncToWire(); });
                if (this.coupon_input) {
                    this.applyCoupon();
                }
            },
            applyRecommendation(rec) {
                const available = new Set((this.catalog.modules || []).map(m => m.key));
                const selected = [];
                (rec.modules || []).forEach(key => {
                    if (!available.has(key)) return;
                    const mod = (this.catalog.modules || []).find(m => m.key === key);
                    selected.push(key);
                    (mod?.requires || []).forEach(r => {
                        if (available.has(r) && !selected.includes(r)) selected.push(r);
                    });
                });
                this.modules = Array.from(new Set(selected));
                this.activeRecommendation = rec.key;

                const quotas = rec.quotas || {};
                Object.keys(quotas).forEach(key => {
                    const def = (this.catalog.quotas || []).find(q => q.key === key);
                    if (!def) return;
                    const value = Math.min(def.max, Math.max(def.min, Number(quotas[key])));
                    this.setQuotaValue(key, value);
                });
            },
            clearRecommendation() {
                this.activeRecommendation = null;
                this.modules = [];
                (this.catalog.quotas || []).forEach(q => {
                    this.setQuotaValue(q.key, q.included);
                });
            },
            modulesFor(groupKey) {
                return (this.catalog.modules || []).filter(m => m.group === groupKey);
            },
            get visibleQuotas() {
                return (this.catalog.quotas || []).filter(quota => {
                    if (!quota.linked_module) return true;
                    return this.modules.includes(quota.linked_module);
                });
            },
            quotaValue(key) {
                if (key === 'employees') return this.employees;
                if (key === 'establishments') return this.establishments;
                if (key === 'screen_devices') return this.screen_devices;
                return 0;
            },
            setQuotaValue(key, value) {
                if (key === 'employees') this.employees = value;
                else if (key === 'establishments') this.establishments = value;
                else if (key === 'screen_devices') this.screen_devices = value;
            },
            isSelected(key) {
                return this.modules.includes(key);
            },
            requiredNames(mod) {
                return (mod.requires || [])
                    .map(key => (this.catalog.modules || []).find(m => m.key === key)?.name || key)
                    .join(', ');
            },
            requiresAnyNames(mod) {
                return (mod.requires_any || [])
                    .map(key => (this.catalog.modules || []).find(m => m.key === key)?.name || key)
                    .join(', ');
            },
            get softDependencyBroken() {
                return (this.catalog.modules || []).some(mod => {
                    if (!this.modules.includes(mod.key)) return false;
                    const any = mod.requires_any || [];
                    if (!any.length) return false;
                    return !any.some(k => this.modules.includes(k));
                });
            },
            toggle(key) {
                this.activeRecommendation = null;
                if (this.isSelected(key)) {
                    this.modules = this.modules.filter(k => k !== key);
                    this.modules = this.modules.filter(k => {
                        const mod = (this.catalog.modules || []).find(m => m.key === k);
                        return !(mod?.requires || []).includes(key);
                    });
                    if (key === 'digital_screens') {
                        const q = (this.catalog.quotas || []).find(item => item.key === 'screen_devices');
                        this.screen_devices = q?.included ?? 1;
                    }
                } else {
                    const mod = (this.catalog.modules || []).find(m => m.key === key);
                    const next = new Set(this.modules);
                    next.add(key);
                    (mod?.requires || []).forEach(r => next.add(r));
                    this.modules = Array.from(next);
                }
            },
            bump(key, delta) {
                this.activeRecommendation = null;
                const quota = (this.catalog.quotas || []).find(q => q.key === key);
                if (!quota) return;
                const current = this.quotaValue(key);
                const next = Math.min(quota.max, Math.max(quota.min, current + delta));
                this.setQuotaValue(key, next);
            },
            setPeriod(period) {
                this.period = period;
            },
            format(value) {
                return Math.round(Number(value || 0)).toLocaleString();
            },
            get baseLineItems() {
                const items = [];
                const platform = this.catalog.platform || {};
                items.push({
                    key: 'platform',
                    label: platform.name,
                    total: platform.price_month,
                    type: 'platform',
                });
                (this.catalog.modules || []).forEach(mod => {
                    if (this.modules.includes(mod.key)) {
                        items.push({ key: mod.key, label: mod.name, total: mod.price_month, type: 'module' });
                    }
                });
                this.visibleQuotas.forEach(quota => {
                    const count = this.quotaValue(quota.key);
                    const extra = Math.max(0, count - quota.included);
                    if (extra > 0) {
                        items.push({
                            key: quota.key + '_extra',
                            label: quota.name + ' (+' + extra + ')',
                            total: extra * quota.price_per_extra_month,
                            type: 'quota',
                        });
                    }
                });
                return items;
            },
            get lineItems() {
                const months = this.period === 'Year' ? (this.catalog.yearly_months_charged || 12) : 1;
                return this.baseLineItems.map(line => {
                    const monthly = Number(line.total || 0);
                    return {
                        ...line,
                        total: monthly * months,
                        meta: this.period === 'Year'
                            ? `${this.format(monthly)} × ${months}`
                            : `/ ${this.labels.per_month}`,
                    };
                });
            },
            get monthlyTotal() {
                return this.baseLineItems.reduce((sum, line) => sum + Number(line.total || 0), 0);
            },
            get periodSubtotal() {
                if (this.period === 'Year') {
                    return this.monthlyTotal * (this.catalog.yearly_months_charged || 12);
                }
                return this.monthlyTotal;
            },
            get periodTotal() {
                return Math.max(0, this.periodSubtotal - Number(this.coupon_discount || 0));
            },
            async applyCoupon() {
                if (this.coupon_loading) return;
                this.coupon_loading = true;
                this.coupon_message = '';
                try {
                    const result = await this.$wire.previewPlanCoupon(this.coupon_input, this.periodSubtotal);
                    if (result?.ok) {
                        this.coupon_code = result.code || '';
                        this.coupon_input = this.coupon_code;
                        this.coupon_discount = Number(result.discount || 0);
                        this.coupon_label = result.label || this.coupon_code;
                        this.coupon_message = result.message || '';
                        this.coupon_ok = true;
                    } else {
                        this.coupon_code = '';
                        this.coupon_discount = 0;
                        this.coupon_label = '';
                        this.coupon_message = result?.message || '';
                        this.coupon_ok = false;
                    }
                    this.syncToWire();
                } catch (e) {
                    this.coupon_code = '';
                    this.coupon_discount = 0;
                    this.coupon_label = '';
                    this.coupon_message = '';
                    this.coupon_ok = false;
                    this.syncToWire();
                } finally {
                    this.coupon_loading = false;
                }
            },
            async refreshCouponDiscount() {
                if (!this.coupon_code) {
                    this.coupon_discount = 0;
                    return;
                }
                try {
                    const result = await this.$wire.previewPlanCoupon(this.coupon_code, this.periodSubtotal);
                    if (result?.ok) {
                        this.coupon_discount = Number(result.discount || 0);
                        this.coupon_label = result.label || this.coupon_code;
                        this.coupon_ok = true;
                    } else {
                        this.clearCoupon(false);
                        this.coupon_message = result?.message || '';
                        this.coupon_ok = false;
                    }
                    this.syncToWire();
                } catch (e) {}
            },
            clearCoupon(clearInput = true) {
                this.coupon_code = '';
                this.coupon_discount = 0;
                this.coupon_label = '';
                this.coupon_message = '';
                this.coupon_ok = false;
                if (clearInput) this.coupon_input = '';
                this.syncToWire();
            },
            syncToWire() {
                try {
                    this.$wire.set(this.wirePath, {
                        period: this.period,
                        modules: this.modules,
                        employees: this.employees,
                        establishments: this.establishments,
                        screen_devices: this.modules.includes('digital_screens') ? this.screen_devices : 0,
                        coupon_code: this.coupon_code || null,
                    });
                } catch (e) {}
            },
        };
    };
</script>