import Resource from '@/api/resource';

class AppResource extends Resource {
  constructor() {
    super('app');
  }
}

export { AppResource as default };
