const defaultPickerOptions = {
  shortcuts: [{
    text: 'Today',
    onClick(picker) {
      const end = new Date();
      const start = new Date();
      picker.$emit('pick', [start, end]);
    },
  }, {
    text: 'Yesterday',
    onClick(picker) {
      const end = new Date(new Date().setDate(new Date().getDate() - 1));
      const start = new Date(new Date().setDate(new Date().getDate() - 1));
      picker.$emit('pick', [start, end]);
    },
  }, {
    text: 'Last 7 days',
    onClick(picker) {
      const end = new Date();
      const start = new Date();
      start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
      picker.$emit('pick', [start, end]);
    },
  }, {
    text: 'Month to date',
    onClick(picker) {
      const end = new Date(new Date(new Date().setMonth(new Date().getMonth() + 1)).setDate(0));
      const start = new Date(new Date().setDate(1));
      picker.$emit('pick', [start, end]);
    },
  }, {
    text: 'The previous Month',
    onClick(picker) {
      const end = new Date(new Date().setDate(0));
      const start = new Date(new Date(new Date().setMonth(new Date().getMonth() - 1)).setDate(1));
      picker.$emit('pick', [start, end]);
    },
  }, {
    text: 'Year to date',
    onClick(picker) {
      const end = new Date(new Date(new Date().setMonth(12)).setDate(0));
      const start = new Date(new Date(new Date().setMonth(0)).setDate(1));
      picker.$emit('pick', [start, end]);
    },
  },
  ],
};

export default defaultPickerOptions;
